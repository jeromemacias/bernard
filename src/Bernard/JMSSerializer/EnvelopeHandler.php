<?php

namespace Bernard\JMSSerializer;

use Bernard\Message\DefaultMessage;
use Bernard\Message\Envelope;
use Bernard\Utils;
use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;

/**
 * @package Bernard
 */
class EnvelopeHandler implements \JMS\Serializer\Handler\SubscribingHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribingMethods()
    {
        return array(array(
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'format'    => 'json',
            'type'      => 'Bernard\Message\Envelope',
            'method'    => 'serializeEnvelope',
        ), array(
            'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
            'format'    => 'json',
            'type'      => 'Bernard\Message\Envelope',
            'method'    => 'deserializeEnvelope',
        ), array(
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'format'    => 'json',
            'type'      => 'Bernard\Message\DefaultMessage',
            'method'    => 'serializeDefaultMessage',
        ), array(
            'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
            'format'    => 'json',
            'type'      => 'Bernard\Message\DefaultMessage',
            'method'    => 'deserializeDefaultMessage',
        ));
    }

    /**
     * @param  AbstractVisitor $visitor
     * @param  Envelope        $envelope
     * @param  string          $type
     * @param  Context         $context
     * @return array
     */
    public function serializeEnvelope(AbstractVisitor $visitor, Envelope $envelope, $type, Context $context)
    {
        $type = array(
            'name' => $envelope->getClass(),
            'params' => array(),
        );

        $data = array(
            'args'      => $context->accept($envelope->getMessage(), $type),
            'class'     => Utils::encodeClassName($envelope->getClass()),
            'timestamp' => $envelope->getTimestamp(),
            'retries'   => $envelope->getRetries(),
        );

        $visitor->setRoot($data);

        return $data;
    }

    /**
     * @param  AbstractVisitor $visitor
     * @param  array           $data
     * @param  string          $type
     * @param  Context         $context
     * @return Envelope
     */
    public function deserializeEnvelope(AbstractVisitor $visitor, array $data, $type, Context $context)
    {
        $data['class'] = Utils::decodeClassString($data['class']);

        $type = array(
            'name' => $data['class'],
            'params' => null,
        );

        if (!class_exists($data['class'])) {
            $data['args']['name'] = current(array_reverse(explode('\\', $data['class'])));
            $type['name'] = 'Bernard\Message\DefaultMessage';
        }

        $envelope = new Envelope($context->accept($data['args'], $type));

        $visitor->setNavigator($context->getNavigator());

        foreach (array('retries', 'timestamp', 'class') as $name) {
            Utils::forceObjectPropertyValue($envelope, $name, $data[$name]);
        }

        return $envelope;
    }

    /**
     * @param  AbstractVisitor $visitor
     * @param  DefaultMessage  $envelope
     * @param  string          $type
     * @param  Context         $context
     * @return array
     */
    public function serializeDefaultMessage(AbstractVisitor $visitor, DefaultMessage $message, $type, Context $context)
    {
        return array('name' => $message->getName()) + get_object_vars($message);
    }

    /**
     * @param  AbstractVisitor $visitor
     * @param  array           $data
     * @param  string          $type
     * @param  Context         $context
     * @return Envelope
     */
    public function deserializeDefaultMessage(AbstractVisitor $visitor, array $data, $type, Context $context)
    {
        return new DefaultMessage($data['name'], $data);
    }
}
