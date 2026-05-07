<?php

namespace App\Service;

use App\Exception\AntivirusException;
use Appwrite\ClamAV\Network;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AntivirusClamAv {

    /**
     * @var ?Network ClamAV Socket Instance
     */
    private ?Network $clamAvInstance  = null;

    private static ?AntivirusClamAv $antivirus = null;

    private function __construct(ParameterBagInterface $envParams) {
        /** @var array $defaultConfig Configuration par défaut */
        $defaultConfig = ['host' => '127.0.0.1', 'port' => '3310'];
        /** @var array $keys Variables définies dans .env / .env.local */
        $keys = ['host' => 'CLAMAV_HOST', 'port' => 'CLAMAV_PORT'];
        $config = [];
        foreach($keys as $idx => $v){
            try{
                $config[$idx] = $envParams->get($v);
            } catch(\Exception $e) {
                $config[$idx] = $defaultConfig[$idx];
            }
        }

        $this->clamAvInstance = new Network($config['host'], $config['port']);
    }

    /**
     * @param ParameterBagInterface $envParams Service pour récupérer
     * les variables dans .env / .env.local
     */
    public static function getInstance(?ParameterBagInterface $envParams = null) : AntivirusClamAv {
        if(self::$antivirus === null){
            if(!($envParams instanceof ParameterBagInterface)){
                throw new \Exception("You must provide a parameter for initialization ! ( AntivirusClamAv::getInstance() )");
            }
            self::$antivirus = new AntivirusClamAv($envParams);
        }

        return self::$antivirus;
    }

    public function getVersion() : string|bool {
        try {
            return $this->clamAvInstance->version();
        } catch(\Exception $error) {
            return false;
        }   
    }

    public function isServerAlive() : bool {
        try {
            return $this->clamAvInstance->ping();
        } catch(\Exception $error) {
            return false;
        }
    }

    public function scanFileStream(string $fullFilePath) : bool|array {
        try {
            return $this->clamAvInstance->fileScanInStream($fullFilePath);
        } catch(\Exception $error) {
            throw AntivirusException::antivirusUnreachableException();
        }
    }
}