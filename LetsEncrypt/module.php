<?php

declare(strict_types=1);

    require_once __DIR__ . '/../libs/vendor/autoload.php';

    class LetsEncrypt extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RegisterPropertyString('EMailAddress', '');
            $this->RegisterPropertyString('Domain', '');
            $this->RegisterPropertyInteger('WebServerID', 0);
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
        }

        public function FetchCertificate()
        {

            //We create a new folder. This will not work for a Module Store subscription.
            //We will need to modify the library to work around this issue or use some "tricks" with a folder inside /tmp
            if (!is_dir(IPS_GetKernelDir() . '_account')) {
                mkdir(IPS_GetKernelDir() . '_account', 0777, true);
            }

            //Create client
            $client = new Rogierw\RwAcme\Api($this->ReadPropertyString('EMailAddress'), IPS_GetKernelDir() . '/_account');

            //Generate public/private key for our account
            if (!$client->account()->exists()) {
                $account = $client->account()->create();
            } else {
                $account = $client->account()->get();
            }

            //Create an order for our domain
            $order = $client->order()->new($account, [$this->ReadPropertyString('Domain')]);

            $this->SendDebug('ORDER', print_r($order, true), 0);

            //Make a domain validation, if required
            if ($order->isPending()) {
                $validationStatus = $client->domainValidation()->status($order);

                $this->SendDebug('STATUS', print_r($validationStatus, true), 0);

                $validationData = $client->domainValidation()->getFileValidationData($validationStatus);

                $this->SendDebug('DATA', print_r($validationData, true), 0);

                if (!is_dir(IPS_GetKernelDir() . 'webfront/.well-known/acme-challenge')) {
                    mkdir(IPS_GetKernelDir() . 'webfront/.well-known/acme-challenge', 0777, true);
                }

                file_put_contents(IPS_GetKernelDir() . 'webfront/.well-known/acme-challenge/' . $validationData[0]['filename'], $validationData[0]['content']);

                $client->domainValidation()->start($account, $validationStatus[0]);
            }

            //Create CSR for certificate creation
            $privateKey = \Rogierw\RwAcme\Support\OpenSsl::generatePrivateKey();
            $csr = \Rogierw\RwAcme\Support\OpenSsl::generateCsr([$this->ReadPropertyString('Domain')], $privateKey);

            $this->SendDebug('PRIVATE KEY', print_r($privateKey, true), 0);

            //Use CSR to generate the certificate
            if ($order->isReady() && $client->domainValidation()->challengeSucceeded($order, \Rogierw\RwAcme\Endpoints\DomainValidation::TYPE_HTTP)) {
                $client->order()->finalize($order, $csr);
            }

            //If successful, fetch our fresh certificate and apply it to the Web Server
            if ($order->isFinalized()) {
                $certificateBundle = $client->certificate()->getBundle($order);

                $this->SendDebug('BUNDLE', print_r($certificateBundle, true), 0);

                // Apply certificates to Web Server instance
                IPS_SetProperty($this->ReadPropertyInteger('WebServerID'), 'Certificate', base64_encode($certificateBundle->certificate));
                IPS_SetProperty($this->ReadPropertyInteger('WebServerID'), 'CertificateAuthority', base64_encode($certificateBundle->fullchain));
                IPS_SetProperty($this->ReadPropertyInteger('WebServerID'), 'PrivateKey', base64_encode($privateKey));
                IPS_ApplyChanges($this->ReadPropertyInteger('WebServerID'));

                echo $this->Translate('Success. Please restart IP-Symcon!');
            }
        }
    }