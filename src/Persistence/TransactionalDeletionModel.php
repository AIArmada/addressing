<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * Atomic Eloquent deletion lifecycle. Reference writes remain in the owning
 * feature actions invoked by the concrete model's deleting event.
 *
 * @internal
 */
abstract class TransactionalDeletionModel extends Model
{
    public function delete(): ?bool
    {
        try {
            return $this->getConnection()->transaction(function (): ?bool {
                $deleted = parent::delete();

                if ($deleted === false) {
                    throw new DeletionCancelled;
                }

                return $deleted;
            });
        } catch (DeletionCancelled) {
            return false;
        }
    }
}
