# LDAPClient
A PHP class to simplify connecting to and searching LDAP directories.


```php
use FaulkJ\LDAPClient

$ldap = new LDAPClient(
   "ldap.forumsys.com",
   "cn=read-only-admin,dc=example,dc=com",
   "password",
   "dc=example,dc=com",
   [
      "id" => "uid"
   ]
);

$res = $ldap->search("(ou=chemist*)", ["cn", "members" => "uniquemember"], null, true);
echo implode("\n", $res->members);
```


```php
if($user = $ldap->login("riemann", "password", ["fullname" => "cn", "mail"])) {
   echo "{$user->fullname} successfully logged in!";
}
else echo "Invalid username or password!";
```

```php
if($ldap->member("mathematicians", "gauss", [
   "id"     => "ou",
   "member" => "uniquemember"
])) {
   "gauss is a mathematician!";
}
```