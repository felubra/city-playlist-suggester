<?php

namespace App\Tests;

use App\Services\LocationTemperatureGetter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Cmfcmf\OpenWeatherMap\Tests\TestHttpClient;
use Http\Factory\Guzzle\RequestFactory;

class LocationTemperatureGetterTest extends KernelTestCase
{
    /**
     * @var LocationTemperatureGetter
     */
    protected $service;

    protected function setUp(): void
    {
        /**
         * Já que queremos testar apenas o processamento de getTemperatureFromCityByName,
         * podemos instanciar o serviço diretamente aqui com um cliente http fake utilizado
         * pela biblioteca OpenWeatherMap em seus testes, de forma a evitar requisições
         * verdadeiras à API.
         */
        $this->service = new LocationTemperatureGetter("teste", new TestHttpClient(), new RequestFactory());
    }

    public function testGetTemperatureFromCityByNameWithEmptyString()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getTemperatureFromCityByName("");
    }

    public function testGetTemperatureFromCityByNameWithWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getTemperatureFromCityByName(0);
    }

    public function testGetTemperatureFromCityByNameReturnType()
    {
        $temperature = $this->service->getTemperatureFromCityByName("teste");
        $this->assertTrue(\is_float($temperature));
    }

    public function testGetTemperatureFromCityByLocationWithUnsupportedFormat()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getTemperatureFromCityByLocation("41 25 01N", "120 58 57W");
    }

    public function testGetTemperatureFromCityByLocationInvalidLatitudeValue() {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getTemperatureFromCityByLocation("-90.1", "10");
    }

    public function testGetTemperatureFromCityByLocationInvalidLongitudeValue() {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getTemperatureFromCityByLocation(0, 180.2);
    }

    public function testGetTemperatureFromCityByLocationReturnType()
    {
        $temperature = $this->service->getTemperatureFromCityByLocation("20.2976", "40.2958");
        $this->assertTrue(\is_float($temperature));
    }
}