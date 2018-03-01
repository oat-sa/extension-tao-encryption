# Tao Encryption 

> This article describes the functioning of tao encryption, focusing of encryption of sensitive data information on a database level.

## Installation

You can add the Tao Encryption as a standard TAO extension to your current TAO instance.

```bash
 $ composer require oat-sa/extension-tao-encryption
```

##  Encrypted services supported

### 1. Results Encryption

- ### Setup Keys on the server tao instance

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupAsymmetricKeys' generate
```
_Note_: 
> This command will generate two keys (public and private) and save them on the filesystem.

_Note_: 
> On Client Tao instance. You have to copy the public key.

_Note_: 
> On Server Tao instance. You need both keys
    
- ### Setup encryption on tao client instance

In order to use the encrypted results service you have to run the following command on the client tao instance.

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedResultStorage'
```

_Note_: 
>  You should use this on tao client instance


- ### Setup decryption on tao server instance.

In order to decrypt your results use the following script by passing a delivery id.

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\DecryptResults' -d <delivery_id>
```

Or by passing the -all argument

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\DecryptResults' -all
```

_Note_: 
> This command will decrypt results and store in the delivery result storage setup.

_Note_: 
>  You should use this on tao server instance

#### 3. Setup Sync Encrypted Result
In order to sync encrypted results the script needs to be run on the server tao instance and client as well.

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedSyncResult'
 ```
_Note_: 
>  You should ran this command on server and client.

### 2. Test State data encryption

In order to use the encrypted state test service you have to run the following command:

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedStateStorage'
```

This service it's using the symmetric algorithm in order to encrypt.

_Note_: 
>  You should ran this command on client tao instance