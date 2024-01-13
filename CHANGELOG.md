# LDAPClient Changelog

### Version 1.5.5 *1/12/2024*
   - Made resolveDN and error methods private
   - Fixed missing property declarations

### Version 1.5.4 *12/2/2022*
   - Bug fixes

### Version 1.5.3 *11/11/2022*
   - Added pagination logic to handle more than a thousand results
   - Speed improvements

### Version 1.5.2 *11/4/2022*
   - Removed backslash stripping from DNs
   - Overhauled search logic to improve speed

### Version 1.5.1 *11/3/2022*
   - Fix for DN resolution handling

### Version 1.5 *10/6/2022*
   - Revised namespacing

### Version 1.4 *9/2/2021*
   - Replaced search's fullDNs parameter with resolveDNs
   - New resolveDN method that will resolve a DN to an id
   - Overhauled the member method to allow matching in both directions

### Version 1.3 *8/28/2021*
   - Initial public release
