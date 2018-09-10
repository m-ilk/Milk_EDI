# Milk EDI

Milk EDI is a PHP library for Amazon Vendor EDI(including 846,850,855,856, and 997). 
With more than a half year of pratice in an ERP system, this library should be stable to use.  
However,due to the limitation on time in this project, there is still some limitation and some features are incompeleted.
But it should be a good starting point for programmers who had no prior exprience with EDI implementation before.

# Getting Started

These instructions will help you to get started and running on your php server for development and testing purposes. 

## Prerequisites

### As2 protocol ###

We used as2 protocol to transfer EDI file between our server and Aamazon server. the following library is a 3rd party as2 protocol library for PHP.

- [AS2Secure](http://www.as2secure.com/)

This library is used for sending EDI file. If you have you other library to use, please change const variable 
```
SEND_FELE_CLASS
```
in **Class/Milk_EDIConfig.php** and implment 
```
SendFile
```
function in **Milk_EDI.php**

### Built With ###

Milk EDI provided 3 html UI that help to access DataBase and visiualize all the data.
The following libraries are **only** reqired to be installed if you need any UI in Controller folder to be worked properly.
if you do not need any UI, this step can be skipped and Controller folder will not be working.

- [Bootstrap](http://getbootstrap.com/)
- [Bootstrap Table](http://bootstrap-table.wenzhixin.net.cn/)
- [jQuery](https://jquery.com/)
- [Live Query](https://github.com/brandonaaron/livequery)
- [Zebra_Datepicker](https://github.com/stefangabos/Zebra_Datepicker)

after install all the libraries above please configure **Class/Milk_EDIConfig.php**


## Installing ##
1. Implement ```MilkObject.php```

```GeneralDateHelper``` is a library for timming.
```OperateDBHelper.php``` is our server database access library.

 Milk EDI use mysql and transaction. Implement and modify any function that is necessary.

2. Run
>Install.php
make sure all tables are succesfully create

## Running the tests ##

See ```TestCase``` folder for all different EDI file type testing.
See ```Milk_EDI.php``` for all functions. 



## Contributing ##

For feature requests, bug reports or submitting pull requests, please ensure you first read CONTRIBUTING.md.