<?php namespace FaulkJ;
/*
 * LDAP Client Class v1.5.3
 *
 * Kopimi 2022 Joshua Faulkenberry
 * Unlicensed under The Unlicense
 * http://unlicense.org/
 */

   class LDAPClient {

      const   version = "1.5.3";

      private $server;
      private $dn;
      private $user;
      private $password;
      private $ldapconn;
      private $options = [
         "dn"     => "distinguishedname",
         "id"     => "samaccountname",
         "member" => "memberof",
         "photo"  => "thumbnailphoto"
      ];
      private $debug   = false;

      public function __construct($server, $bindDN, $pass, $baseDN, array $options = []) {
         $this->server   = $server;
         $this->bindDN   = $bindDN;
         $this->password = $pass;
         $this->baseDN   = $baseDN;
         $this->options  = array_merge($this->options, $options);
         $this->connect();
      }

      private function connect() {
         $this->ldapconn = ldap_connect($this->server);
         ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
         ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
      }

      private function bind($dn, $pass) {
         if(!$dn || !$pass) return false;
         else return ldap_bind($this->ldapconn, $dn, $pass);
         if($this->debug && ldap_error($this->ldapconn) != "Success") $this->error("Binding failed.", $dn);
      }

      private function unbind() {
         ldap_unbind($this->ldapconn);
         $this->connect();
      }

      public function login($id, $pass, $attr) {
         if(!$id || !$pass) return false;

         $res = false;
         if($usr = $this->search("({$this->options['id']}={$id})", $attr)) {
            if(@$this->bind($usr->dn, $pass)) {
               $this->unbind();
               $res = $usr;
            }
         }

         if($this->debug && ldap_error($this->ldapconn) != "Success") $this->error("Login failed.", $id);

         return $res;
      }

      public function search($filter, $attr = [], $dn = null, $resolveDNs = false, $stayBound = false) {
         if($stayBound || $this->bind($this->bindDN, $this->password)) {
            $dn = (array) ($dn ? $dn :$this->baseDN);

            $atx = (array) $attr;
            sort($atx);
            $list = [];

            for($x = 0; $x < count($dn); $x++) {
               $cookie = "";
               do {
                  if($result = ldap_search($this->ldapconn, $dn[$x], $filter, $atx, 0, 0, 0, LDAP_DEREF_NEVER, [['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => 500, 'cookie' => $cookie]]])) {
                     if($entries = ldap_get_entries($this->ldapconn, $result)) {
                        for ($i = 0; $i < $entries["count"]; $i++) {
                           $res = [];
                           $res["dn"] = $entries[$i]["dn"];

                           foreach((array) $attr as $key => $at) {
                              $lbl = is_string($key) ? $key : $at;
                              if($at == $this->options['photo']) $res[$lbl] = $entries[$i][$at][0];
                              else {
                                 $res[$lbl] = [];
                                 if(isset($entries[$i][strtolower($at)])) {
                                    $values = $entries[$i][strtolower($at)];

                                    if($values["count"] == 1) {
                                       $res[$lbl] = $values[0];
                                       if($resolveDNs && strpos(strtoupper($res[$lbl]), "DC=") !== false) $res[$lbl] = $this->resolveDN($res[$lbl]);
                                    }
                                    else for ($x=0; $x < $values["count"]; $x++) {
                                       $v = $values[$x];
                                       if($resolveDNs && strpos(strtoupper($v), "DC=") !== false) $v = $this->resolveDN($v);
                                       array_push($res[$lbl], $v);
                                    }
                                    if(is_array($res[$lbl])) sort($res[$lbl]);
                                 }
                                 if(is_array($res[$lbl]) && count($res[$lbl]) == 0) $res[$lbl] = null;
                              }
                           }
                           array_push($list, new LDAPClient\LDAPRecord($res));
                        }
                     }
                     ldap_parse_result($this->ldapconn, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
                     if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
                     else $cookie = '';
                  }
               } while(!empty($cookie));
            }

            if(!$stayBound) $this->unbind();

            if(count($list) == 0) return null;
            else if(count($list) == 1) return current($list);
            else return $list;
         }
         else {
            if($this->debug && ldap_error($this->ldapconn) != "Success") $this->error("Search binding failed.", $this->bindDN);
            return false;
         }
      }

      public function getJSON($filter, $attr = [], $dn = false, $fullDNs = false) {
         $list = $this->search($filter, $attr, $dn, $fullDNs);
         return json_encode($list);
      }

      public function resolveDN($dn, $class = "top") {
         $id = null;
         if($rec = $this->search("(objectclass=$class)", $this->options['id'], $dn, false, true)) {
            if(is_array($rec)) {
               foreach($rec as $i) if(isset($i->{$this->options['id']})) $id = $i->{$this->options['id']};
            }
            else $id = $rec->{$this->options['id']};
         }
         return $id;
      }

      public function member($user, $group, array $options = []) {
         $opt  = array_merge($this->options, $options);
         if($usr = $this->search("({$opt['id']}=$user)", [$opt['member']], null, true)) {
            return isset($usr->{$opt['member']}) ? count(array_intersect(array_map('strtolower', (array) $group), array_map('strtolower', (array) $usr->{$opt['member']}))) > 0 : false;
         }
         return null;
      }

      public function photo($user) {
         $p = $this->search("({$this->options['id']}=$user)", ["photo" => $this->options['photo']]);

         if(isset($p->photo)) {
            header('content-type: image/jpeg');
            die($p->photo);
         }
         else {
            $im = imagecreatetruecolor(100, 100);
            $red = imagecolorallocate($im, 179, 179, 179);
            imagefill($im, 0, 0, $red);
            header('Content-type: image/png');
            imagepng($im);
            imagedestroy($im);
         }
         exit;
      }

      public function debug($dbg = null) {
         if($dbg !== null) $this->debug = !($dbg == false);
         else return $this->debug;
         return $this;
      }

      public function error($msg, $usr = null) {
         echo("\n\n$msg\n" . ldap_error($this->ldapconn) . "\n");
         if($usr) echo "Bind DN: $usr\n";
      }

   }

?>