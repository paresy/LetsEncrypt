<?php

    require_once __DIR__ . "/../libs/vendor/autoload.php";

	class LetsEncrypt extends IPSModule {

		public function Create()
		{
			//Never delete this line!
			parent::Create();

            $this->RegisterPropertyString("EMailAddress", "");
			$this->RegisterPropertyString("Domain", "");
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
            //mkdir(IPS_GetKernelDir() . "_account", 0777, true);

            $client = new Rogierw\RwAcme\Api($this->ReadPropertyString('EMailAddress'), IPS_GetKernelDir() . '/_account');

            if (!$client->account()->exists()) {
                $account = $client->account()->create();
            } else {
                $account = $client->account()->get();
            }

            $order = $client->order()->new($account, [$this->ReadPropertyString("Domain")]);

            $this->SendDebug("ORDER", print_r($order, true), 0);

            $validationStatus = $client->domainValidation()->status($order);

            $this->SendDebug("STATUS", print_r($validationStatus, true), 0);

            $validationData = $client->domainValidation()->getFileValidationData($validationStatus);

            $this->SendDebug("DATA", print_r($validationData, true), 0);

            mkdir(IPS_GetKernelDir() . "webfront/.well-known/acme-challenge", 0777, true);

            file_put_contents(IPS_GetKernelDir() . "webfront/.well-known/acme-challenge/" . $validationData[0]['filename'], $validationData[0]['content']);

            $client->domainValidation()->start($account, $validationStatus[0]);

            $privateKey = \Rogierw\RwAcme\Support\OpenSsl::generatePrivateKey();
            $csr = \Rogierw\RwAcme\Support\OpenSsl::generateCsr([$this->ReadPropertyString("Domain")], $privateKey);

            if ($order->isReady() && $client->domainValidation()->challengeSucceeded($order, DomainValidation::TYPE_HTTP)) {
                $client->order()->finalize($order, $csr);
            }

            if ($order->isFinalized()) {
                $certificateBundle = $client->certificate()->getBundle($order);
            }

        }

	}