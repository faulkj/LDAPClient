
# LDAPClient

LDAPClient is a PHP library designed to make LDAP operations easy and flexible. From basic queries to advanced LDAP functionalities, this library offers a wide range of features.

## Features

- Simplified LDAP Connection
- User Authentication
- Advanced Search Queries
- Debugging Support

## Installation

```bash
composer require faulkj/ldapclient
```

## Methods & Usage

### LDAPClient Class

#### `__construct($server, $bindDN, $pass, $baseDN, $options)`

Initiate a connection to the LDAP server.

- `$server`: The LDAP server URL
- `$bindDN`: The DN to bind with
- `$pass`: The password for binding
- `$baseDN`: The base DN for the directory
- `$options`: List of LDAP fields to pull.  Allows for custom attribute mappings to align with the specific LDAP system you're working with.

**Example:**

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
```

#### `debug($dbg)`

Toggle debugging output for LDAP operations.  When enabled, this provides verbose output for troubleshooting.

- `$dbg`: Boolean to enable or disable debugging

**Example:**

```php
$ldap->debug(true);
```

Returns:

- Current debug state if no parameter is provided
- The LDAPClient object itself, allowing for method chaining, if a parameter is provided

#### `getJSON($filter, $attr = [], $dn = false, $fullDNs = false)`

Converts the search result to a JSON format.

- `$filter`: LDAP filter
- `$attr`: Attributes to fetch
- `$dn`: Optional DN to search under
- `$resolveDNs`: Whether to resolve DNs
- `$stayBound`: Whether to stay bound

**Example:**

```php
die($ldap->getJSON("(ou=chemist*)", ["cn", "members" => "uniquemember"], null, true));
```

Returns: JSON-formatted string representing the search results.

#### `login($id, $pass, $attr)`

Authenticate a user and return their attributes.

- `$id`: User ID
- `$pass`: Password
- `$attr`: Attributes to fetch

**Example:**

```php
if($user = $ldap->login("riemann", "password", ["fullname" => "cn", "mail"])) {
   echo "{$user->fullname} successfully logged in!";
}
else echo "Invalid username or password!";
```

Returns:

- User object with specified attributes if successful
- `false` if authentication fails
- `null` if the user is not found

#### `member($user, $group, $options = [])`

Check if a user is a member of a specific LDAP group or groups.

- `$user`: The user ID to check
- `$group`: Group or array of groups to check membership
- `$options`: Optional array for additional attribute mappings (merges with default options)

Example:

```php
if($ldap->member("mathematicians", "gauss", [
   "id"     => "ou",
   "member" => "uniquemember"
])) {
   echo "gauss is a mathematician!";
}
```

Returns:

- `true` if the user is a member
- `false` if the user is not a member
- `null` if the user is not found

#### `photo($user)`

Retrieves the photo for a specified user.

**Example:**

```php
$ldap->photo("simmons");
```

Returns: Outputs the image directly to the screen if available, a blank image if not

#### `search($filter, $attr, $dn, $resolveDNs, $stayBound)`

Perform an LDAP search.

- `$filter`: LDAP filter
- `$attr`: Attributes to fetch
- `$dn`: Optional DN to search under
- `$resolveDNs`: Whether to resolve DNs
- `$stayBound`: Whether to stay bound

**Example:**

```php
$res = $ldap->search(
   "(ou=chemist*)",
   [
      "cn",
      "members" => "uniquemember"
   ],
   null,
   true
);
echo implode("\n", $res->members);
```

Returns:

- Array of LDAP records if multiple records are found
- Single LDAP record if only one is found
- null if no records are found