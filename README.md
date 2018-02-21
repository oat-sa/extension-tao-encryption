# Tao Encryption 

> This article describes the functioning of tao encryption, focusing of encryption of sensitive data information on a database level.

## Installation

You can add the Tao Encryption as a standard TAO extension to your current TAO instance.

```bash
 $ composer require oat-sa/extension-tao-encryption
```

##  Encrypted services supported

### 1. Results data encryption

In order to use the encrypted results service you have to run the fallowing command

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedResultStorage'
```

#### 1. Setup encryption

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupAsymmetricKeys' generate
```

_Note_: 
> This command will generate two keys (public and private) and save them on the filesystem.

The public key it's used for encryption and private key for decryption.
 In case we need encryption on different server passing the public key should be enough.


#### 2. Setup decryption

In order to decrypt your results use the fallowing script by passing a delivery id.

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\DecryptResults' <delivery_id>
```
_Note_: 
> This command will decrypt results and store in the delivery result storage setup.

### 2. Test State data encryption

In order to use the encrypted state test service you have to run the fallowing command:

```bash
 $ sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedStateStorage'
```

This service it's using the symmetric algorithm in order to encrypt.

The key provider is a configurable option which can be changed in the config file:
`taoEncryption/symmetricDeliveryExecutionProvider`
