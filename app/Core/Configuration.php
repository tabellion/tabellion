<?php 

use Symfony\Component\Yaml\Yaml;


class Configuration
{
    private $config = [];
    private $default = [
        'default_locale' => 'fr',
        'nom' => 'Tabellion',
        'sigle' => '',
        'libele' => '',
        'description' => 'Indexation des archives de france',
        'domaine_tabellion' => 'http://localhost:8080',
        'domaine_site' => 'http://localhost:8080',
        'domaine_mon-compte' => 'http://localhost:8080/mon-compte/',
        'database' => [
            'host' => 'localhost', 
            'port' => 3306,
            'user' => 'root', 
            'pass' => 'secret', 
            'dbname' => 'tabellion'
        ],
        'local_storage' => '/storage',
        'local_logs' => '/logs',
    ];

    public function __construct(bool $onInstallOrUpdate = false)
    {
        if ($onInstallOrUpdate === true) {
            $this->_loadConfig();
            $this->_checkRequirements();
        }
        $this->_loadConfig();
    }

    public function get(string $field)
    {
        if ($this->config[$field]) {
            return $this->config[$field];
        }
        throw new Exception("Configuration of $field not found.", 1);
    }

    public function getAll(): array
    {
        return $this->config;
    }

/*     public function add(string $field, string $value)
    {
        $this->config[$field] = $value;
        $this->_save();
    } */

    private function _onInstallOrUpdate()
    {
        // put file writable
        // write file
        // put file unwritable
    }

    private function _loadConfig()
    {
        if (!file_exists(__DIR__ . '/../../config.yaml.cfg')) {
            throw new Exception("Configuration file not found. Please check your install", 1);
        }
        $this->config = Yaml::parseFile(__DIR__ . '/../../config.yaml.cfg');
    }

    private function _checkRequirements()
    {
        foreach ($this->default as $key => $value) {
            if (!array_key_exists($key, $this->config)) {
                $this->config[$key] = $value;
            }
        }

        foreach ($this->config as $key => $value) {
            if (!array_key_exists($key, $this->default)) {
                unset($this->config[$key]);
            }
        }
        $this->_save();
    }

    private function _save()
    {
        $new_config = Yaml::dump($this->config);
        file_put_contents(__DIR__ . '/../../config.yaml.cfg', $new_config);
    }
}