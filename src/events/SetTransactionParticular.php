<?php

namespace batchnz\flo2cash\events;

use yii\base\Event;

/**
 * Class SetTransactionParticular
 *
 * @author Josh Smith <josh@batch.nz>
 * @since  1.0
 */
class SetTransactionParticular extends Event
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
