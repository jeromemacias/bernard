<?php

namespace Bernard\Serializer;

use Bernard\Message\Envelope;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

/**
 * @package Bernard
 */
class JMSSerializer implements \Bernard\Serializer
{
    protected $serializer;

    /**
     * @param JMSSerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(Envelope $envelope)
    {
        $context = SerializationContext::create()
            ->setSerializeNull(true);

        return $this->serializer->serialize($envelope, 'json', $context);
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize($deserialized)
    {
        return $this->serializer->deserialize($deserialized, 'Bernard\Message\Envelope', 'json');
    }
}
