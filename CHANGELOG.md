# LDAPClient Changelog

***2022-11-11*** Version 1.5.3
   - Added pagination logic to handle more than a thousand results
   - Speed improvements

***2022-11-04*** Version 1.5.2
   - Removed backslash stripping from DNs
   - Overhauled search logic to improve speed

***2022-11-03*** Version 1.5.1
   - Fix for DN resolution handling

***2022-10-06*** Version 1.5
   - Revised namespacing

***2021-09-02*** Version 1.4
   - Replaced search's fullDNs parameter with resolveDNs
   - New resolveDN method that will resolve a DN to an id
   - Overhauled the member method to allow matching in both directions

***2021-08-28*** Version 1.3
   - Initial public release
