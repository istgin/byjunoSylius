<?php

namespace Ij\SyliusByjunoPlugin\Entity;

use DateTimeInterface;
use Ij\SyliusByjunoPlugin\Repository\ByjunoLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity(repositoryClass=ByjunoLogRepository::class)
 */
class ByjunoLog implements ResourceInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="requestid", type="string", precision=0, scale=0, nullable=false, unique=false)
     */
    protected $requestId;

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    /**
     * @ORM\Column(name="requesttype", type="string", precision=0, scale=0, nullable=false, unique=false)
     */
    protected $requestType;

    public function getRequestType(): string
    {
        return $this->requestType;
    }

    public function setRequestType(string $requestType): void
    {
        $this->requestType = $requestType;
    }

    /**
     * @ORM\Column(name="firstname", type="string", precision=0, scale=0, nullable=false, unique=false)
     */
    protected $firstname;

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @ORM\Column(name="lastname", type="string", precision=0, scale=0, nullable=false, unique=false)
     */
    protected $lastname;

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @ORM\Column(name="ip", type="string", precision=0, scale=0, nullable=false, unique=false)
     */
    protected $ip;

    public function getIP(): string
    {
        return $this->ip;
    }

    public function setIP(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @ORM\Column(name="byjunostatus", type="string", precision=0, scale=0, nullable=false, unique=false)
     */
    protected $byjunoStatus;

    public function getByjunoStatus(): string
    {
        return $this->byjunoStatus;
    }

    public function setByjunoStatus(string $byjunoStatus): void
    {
        $this->byjunoStatus = $byjunoStatus;
    }

    /**
     * @ORM\Column(name="xml_request", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    protected $xmlRequest;

    public function getXmlRequest(): string
    {
        return $this->xmlRequest;
    }

    public function setXmlRequest(string $xmlRequest): void
    {
        $this->xmlRequest = $xmlRequest;
    }

    /**
     * @ORM\Column(name="xml_response", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    protected $xmlResponse;

    public function getXmlResponse(): string
    {
        return $this->xmlResponse;
    }

    public function setXmlResponse(string $xmlResponse): void
    {
        $this->xmlResponse = $xmlResponse;
    }

    /** @psalm-suppress MissingReturnType */
    public function getId()
    {
       return $this->id;
    }
}
