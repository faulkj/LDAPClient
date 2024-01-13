# LDAPClient Changelog

### Version 1.5.5 *2024-01-12*
   - Made resolveDN and error methods private
   - Fixed missing declarations

### Version 1.5.4 *2022-12-02*
   - Bug fixes

### Version 1.5.3 *2022-11-11*
   - Added pagination logic to handle more than a thousand results
   - Speed improvements

### Version 1.5.2 *2022-11-04*
   - Removed backslash stripping from DNs
   - Overhauled search logic to improve speed

### Version 1.5.1 *2022-11-03*
   - Fix for DN resolution handling

### Version 1.5 *2022-10-06*
   - Revised namespacing

### Version 1.4 *2021-09-02*
   - Replaced search's fullDNs parameter with resolveDNs
   - New resolveDN method that will resolve a DN to an id
   - Overhauled the member method to allow matching in both directions

### Version 1.3 *2021-08-28*
   - Initial public release
