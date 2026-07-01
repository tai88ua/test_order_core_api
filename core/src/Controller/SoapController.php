<?php

namespace App\Controller;

use App\Service\SoapOrderService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SoapController extends AbstractController
{
    public function __construct(
        private readonly SoapOrderService $soapOrderService
    ) {}

    #[Route('/api/soap', name: 'app_soap', methods: ['GET', 'POST'])]
    #[OA\Post(
        path: '/api/soap',
        summary: 'SOAP эндпоинт для сохранения (создания) данных заказа',
        description: 'Принимает SOAP-запрос в формате XML для создания нового заказа со списком позиций (поддерживает WSDL).',
        tags: ['SOAP'],
        requestBody: new OA\RequestBody(
            description: 'SOAP XML-запрос',
            required: true,
            content: new OA\MediaType(
                mediaType: 'text/xml',
                schema: new OA\Schema(
                    type: 'string',
                    example: '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:typ="http://localhost/soap">
   <soapenv:Header/>
   <soapenv:Body>
      <typ:createOrder>
         <orderData>
            <name>SOAP Order Test</name>
            <clientName>Ivan</clientName>
            <clientSurname>Ivanov</clientSurname>
            <email>ivan@example.com</email>
            <locale>ru</locale>
            <currency>EUR</currency>
            <measure>m</measure>
            <payType>1</payType>
            <articles>
               <article>
                  <articleId>123</articleId>
                  <amount>15.5</amount>
                  <price>45.99</price>
                  <weight>2.5</weight>
               </article>
            </articles>
         </orderData>
      </typ:createOrder>
   </soapenv:Body>
</soapenv:Envelope>'
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешный SOAP XML ответ с информацией о созданном заказе',
        content: new OA\MediaType(
            mediaType: 'text/xml',
            schema: new OA\Schema(type: 'string')
        )
    )]
    public function index(Request $request): Response
    {
        $host = $request->getSchemeAndHttpHost();

        if ($request->isMethod('GET')) {
            $wsdl = $this->generateWsdl($host);
            return new Response($wsdl, 200, [
                'Content-Type' => 'text/xml; charset=utf-8'
            ]);
        }

        // Для работы SoapServer в режиме WSDL сохраняем динамический WSDL во временный файл
        $wsdlFile = '/tmp/soap_service.wsdl';
        file_put_contents($wsdlFile, $this->generateWsdl($host));

        $soapServer = new \SoapServer($wsdlFile);
        $soapServer->setObject($this->soapOrderService);

        ob_start();
        try {
            $soapServer->handle($request->getContent());
        } catch (\Throwable $e) {
            $soapServer->fault('Sender', $e->getMessage());
        }
        $responseContent = ob_get_clean();

        return new Response($responseContent, 200, [
            'Content-Type' => 'text/xml; charset=utf-8'
        ]);
    }

    /**
     * Генерирует динамическое WSDL описание для SOAP службы.
     */
    private function generateWsdl(string $host): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<definitions name="SoapOrderService"
             targetNamespace="{$host}/api/soap"
             xmlns:tns="{$host}/api/soap"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
             xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
             xmlns="http://schemas.xmlsoap.org/wsdl/">

    <types>
        <xsd:schema targetNamespace="{$host}/api/soap">
            
            <xsd:complexType name="Article">
                <xsd:all>
                    <xsd:element name="articleId" type="xsd:int"/>
                    <xsd:element name="amount" type="xsd:float"/>
                    <xsd:element name="price" type="xsd:float"/>
                    <xsd:element name="priceEur" type="xsd:float" minOccurs="0"/>
                    <xsd:element name="currency" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="measure" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="weight" type="xsd:float" minOccurs="0"/>
                    <xsd:element name="multiplePallet" type="xsd:int" minOccurs="0"/>
                    <xsd:element name="packagingCount" type="xsd:float" minOccurs="0"/>
                    <xsd:element name="pallet" type="xsd:float" minOccurs="0"/>
                    <xsd:element name="packaging" type="xsd:float" minOccurs="0"/>
                    <xsd:element name="swimmingPool" type="xsd:boolean" minOccurs="0"/>
                </xsd:all>
            </xsd:complexType>

            <xsd:complexType name="ArticlesArray">
                <xsd:sequence>
                    <xsd:element name="article" type="tns:Article" minOccurs="0" maxOccurs="unbounded"/>
                </xsd:sequence>
            </xsd:complexType>

            <xsd:complexType name="OrderData">
                <xsd:all>
                    <xsd:element name="name" type="xsd:string"/>
                    <xsd:element name="clientName" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="clientSurname" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="email" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="locale" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="currency" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="measure" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="payType" type="xsd:int" minOccurs="0"/>
                    <xsd:element name="hash" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="userId" type="xsd:int" minOccurs="0"/>
                    <xsd:element name="token" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="number" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="status" type="xsd:int" minOccurs="0"/>
                    <xsd:element name="vatType" type="xsd:int" minOccurs="0"/>
                    <xsd:element name="vatNumber" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="taxNumber" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="discount" type="xsd:int" minOccurs="0"/>
                    <xsd:element name="delivery" type="xsd:float" minOccurs="0"/>
                    <xsd:element name="deliveryType" type="xsd:int" minOccurs="0"/>
                    <xsd:element name="deliveryIndex" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="deliveryCountry" type="xsd:int" minOccurs="0"/>
                    <xsd:element name="deliveryRegion" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="deliveryCity" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="deliveryAddress" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="deliveryBuilding" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="deliveryPhoneCode" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="deliveryPhone" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="companyName" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="description" type="xsd:string" minOccurs="0"/>
                    <xsd:element name="weightGross" type="xsd:float" minOccurs="0"/>
                    <xsd:element name="articles" type="tns:ArticlesArray" minOccurs="0"/>
                </xsd:all>
            </xsd:complexType>

            <xsd:complexType name="CreateOrderResponse">
                <xsd:all>
                    <xsd:element name="success" type="xsd:boolean"/>
                    <xsd:element name="orderId" type="xsd:int"/>
                    <xsd:element name="hash" type="xsd:string"/>
                    <xsd:element name="message" type="xsd:string"/>
                </xsd:all>
            </xsd:complexType>

            <xsd:element name="createOrder">
                <xsd:complexType>
                    <xsd:sequence>
                        <xsd:element name="orderData" type="tns:OrderData"/>
                    </xsd:sequence>
                </xsd:complexType>
            </xsd:element>

            <xsd:element name="createOrderResponse">
                <xsd:complexType>
                    <xsd:sequence>
                        <xsd:element name="return" type="tns:CreateOrderResponse"/>
                    </xsd:sequence>
                </xsd:complexType>
            </xsd:element>

        </xsd:schema>
    </types>

    <message name="createOrderRequest">
        <part name="parameters" element="tns:createOrder"/>
    </message>
    <message name="createOrderResponse">
        <part name="parameters" element="tns:createOrderResponse"/>
    </message>

    <portType name="SoapOrderPortType">
        <operation name="createOrder">
            <input message="tns:createOrderRequest"/>
            <output message="tns:createOrderResponse"/>
        </operation>
    </portType>

    <binding name="SoapOrderBinding" type="tns:SoapOrderPortType">
        <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="createOrder">
            <soap:operation soapAction="{$host}/api/soap#createOrder"/>
            <input>
                <soap:body use="literal"/>
            </input>
            <output>
                <soap:body use="literal"/>
            </output>
        </operation>
    </binding>

    <service name="SoapOrderService">
        <port name="SoapOrderPort" binding="tns:SoapOrderBinding">
            <soap:address location="{$host}/api/soap"/>
        </port>
    </service>
</definitions>
XML;
    }
}
