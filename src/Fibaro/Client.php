<?php

namespace Irekk\Fibaro;

class Client
{
    
    /**
     * @var array $map
     */
    protected $map = [];

    /**
     * Constructor
     * Creates instance of HTTP connector
     * 
     * Accepted options:
     * $options['url'] - Base URL of Home Center API, eg http://192.168.0.2
     * $options['user'] - API user name
     * $options['pass'] - API user password
     * $options['map'] - Structure of devices IDs
     * 
     * @author ikubicki
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $headers = [
            'Authorization' => 'Basic ' . base64_encode(sprintf('%s:%s', $options['user'], $options['pass'])),
        ];
        $this->connector = $this->getHttpConnector($options['url'], $headers);
        $this->map = (array) ($options['map'] ?? []);
    }

    /**
     * Returns all metrics
     * 
     * @author ikubicki
     * @return array
     */
    public function getMetrics()
    {
        $metrics = [];
        $this->_cache = null;
        foreach($this->map as $category => $id)
        {
            $metrics[$category] = $this->processDevice($id);
        }
        return $metrics;
    }

    /**
     * @var array $_devices
     */
    private $_devices = [];

    /**
     * Pulls device information and wraps into sensor object
     * 
     * @author ikubicki
     * @param string $id
     * @return Sensors\Generic
     */
    protected function processDevice($id)
    {
        if (is_array($id)) {
            foreach($id as $subCategory => $subId) {
                $id[$subCategory] = $this->processDevice($subId);
            }
            return $id;
        }
        if (isset($this->_devices[$id])) {
            return $this->_devices[$id];
        }
        $device = $this->pullDevice($id);
        $this->_devices[$id] = $device ? $this->createWrapper($device) : $device;
        return $this->_devices[$id];
    }

    /**
     * Retrieve information about specific Fibaro device
     * 
     * @author ikubicki
     * @param string $id
     * @return array
     */
    protected function pullDevice($id)
    {
        $devices = $this->pullData()['devices'];
        return $devices[$id] ?? false;
    }
    
    /**
     * @var array|null $_cache
     */
    private $_cache;

    /**
     * Retrieves information about fibaro devices
     * 
     * @author ikubicki
     * @return array
     */
    protected function pullData()
    {
        if ($this->_cache === null) {
            $this->_cache = ['devices' => []];
            $response = $this->connector->get('/api/devices');
            if ($response->getStatusCode() < 300) {
                $devices = json_decode($response->getBody()->getContents(), true);
                foreach ($devices as $device) {
                    if (substr($device['baseType'], 0, 10) == 'com.fibaro')  {
                        $this->_cache['devices'][$device['id']] = $device;
                    }
                }
            }
        }
        return $this->_cache;
    }

    /**
     * Returns instance of Fibaro device wrapper
     * 
     * @author ikubicki
     * @param array $device
     * @return Sensors\Generic
     */
    protected function createWrapper(array $device)
    {
        // other method is to check $device['type'] but it's not covering all devices
        $type = $device['properties']['quickAppVariables'][2]['value'] ?? null;
        switch($type) {
            case 'WindAngle':
            case 'GustAngle':
                return new Sensors\WindAngle($device);
            case 'WindStrength':
            case 'GustStrength':
                return new Sensors\Wind($device);
            case 'Rain':
            case 'sum_rain_1':
            case 'sum_rain_24':
                return new Sensors\Rain($device);
            case 'Pressure':
                return new Sensors\Pressure($device);
            case 'Humidity':
                return new Sensors\Humidity($device);
            case 'Temperature':
                return new Sensors\Temperature($device);
        }
        return new Sensors\Generic($device);
    }

    /**
     * @var \GuzzleHttp\ClientInterface $connector
     */
    protected $connector;

    /**
     * Creates instance of http connector
     * 
     * @author ikubicki
     * @param string $url
     * @param array $headers
     * @return \GuzzleHttp\ClientInterface
     */
    protected function getHttpConnector($url, array $headers = [])
    {
        if (empty($this->connector)) {
            $this->connector = new \GuzzleHttp\Client([
                'base_uri' => $url,
                'headers' => $headers,
                'verify' => false,
                'http_errors' => false,
            ]);
        }
        return $this->connector;
    }
}