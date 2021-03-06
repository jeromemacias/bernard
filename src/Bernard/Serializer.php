<?php

namespace Bernard;

use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
interface Serializer
{
    /**
     * @param  Envelope $envelope
     * @return string
     */
    public function serialize(Envelope $envelope);

    /**
     * @return Envelope
     */
    public function deserialize($serialized);
}
