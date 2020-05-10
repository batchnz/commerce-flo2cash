<?php

namespace batchnz\flo2cash\events;

use yii\base\Event;

/**
 * Class SetTransactionReference
 *
 * @author Josh Smith <josh@batch.nz>
 * @since  1.0
 */
class SetTransactionReference extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var string The transaction particular
     */
    public $particular;

    /**
     * @var Transaction The transaction
     */
    public $transaction;
}
