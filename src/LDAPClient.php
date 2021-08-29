<?php namespace FaulkJ\LDAPClient;
   /*
    * LDAP Client Class v1.3
    *
    * Kopimi 2021 Joshua Faulkenberry
    * Unlicensed under The Unlicense
    * http://unlicense.org/
    */

   class LDAPClient {

      const   version = "1.3";

      private $server;
      private $dn;
      private $user;
      private $password;
      private $ldapconn;
      private $options = [
         "identifier" => "samaccountname",
         "dn"         => "distinguishedname",
         "photo"      => "thumbnailphoto",
         "member"     => "memberof"
      ];
      private $debug   = false;

      public function __construct($server, $bindDN, $pass, $baseDN, array $options = []) {
         $this->server   = $server;
         $this->bindDN     = $bindDN;
         $this->password = $pass;
         $this->baseDN       = $baseDN;
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

      private function cvtQuotes($string) {
         return str_replace([
                  chr(145),
                  chr(146),
                  chr(147),
                  chr(148),
                  chr(151)
         ], [
                  "'",
                  "'",
                  '"',
                  '"',
                  ' - '
         ], $string);
      }

      public function login($id, $pass, $attr) {
         if(!$id || !$pass) return false;

         $res = false;
         if($usr = $this->search("({$this->options['identifier']}={$id})", $attr)) {
            if($this->bind($usr->dn, $pass)) {
               $this->unbind();
               $res = $usr;
            }
         }

         if($this->debug && ldap_error($this->ldapconn) != "Success") $this->error("Login failed.", $id);

         return $res;
      }

      public function search($filter, $attr = [], $dn = null, $fullDNs = false) {
         if($this->bind($this->bindDN, $this->password)) {
            $dn = (array) ($dn ? $dn :$this->baseDN);

            $atx = (array) $attr;
            sort($atx);
            $list = [];

            for($x = 0; $x < count($dn); $x++) {
               if($result = ldap_search($this->ldapconn, $dn[$x], $filter, $atx, 0, 0)) {
                  //if ( ldap_errno( $this->ldapconn ) === 4 ) echo 'Partial search results returned';

                  if($entry = ldap_first_entry($this->ldapconn, $result)) {
                     do {
                        $usr = [];
                        $usr["dn"] = ldap_get_dn($this->ldapconn, $entry);

                        foreach((array) $attr as $key => $at) {
                           $lbl = is_string($key) ? $key : $at;

                           if($at == $this->options['photo']) {
                              if($values = @ldap_get_values_len($this->ldapconn, $entry, $at)) {
                                 $usr[$lbl] = $values[0];
                              }
                           }
                           else if($values = @ldap_get_values($this->ldapconn, $entry, $at)) {
                              if($values["count"] == 1) {
                                 $v = $this->cvtQuotes($values[0]);
                                 $usr[$lbl] = (!$fullDNs && strpos($v, "CN=") !== false && strtolower($at) != $this->options['dn']) ? str_replace('\\2C', ',', trim(ldap_explode_dn($v, 1)[0])) : $v;
                              }
                              else {
                                 $usr[$lbl] = [];
                                 for ($i=0; $i < $values["count"]; $i++) {
                                    $v = $this->cvtQuotes($values[$i]);
                                    array_push($usr[$lbl], !$fullDNs ? ldap_explode_dn($v, 1)[0] : $v);
                                 }
                                 sort($usr[$lbl]);
                              }
                           }
                        }
                        array_push($list, new LDAPRecord($usr));
                     } while ($entry = ldap_next_entry($this->ldapconn, $entry));
                  }
               }
            }

            $this->unbind();

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

      public function member($user, $group) {
         $usr = $this->search("({$this->options['identifier']}=$user)", [$this->options['member']]);
         return isset($usr->$this->options['member']) ? count(array_intersect((array) $group, (array) $usr->$this->options['member'])) > 0 : false;
      }

      public function photo($user) {
         $p = $this->search("({$this->options['identifier']}=$user)", ["photo" => $this->options['photo']]);

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