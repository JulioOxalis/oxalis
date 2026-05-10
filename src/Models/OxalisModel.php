<?php
namespace Oxalis\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/*
 * Conditionally extends the MongoDB Eloquent model when available.
 * This allows Oxalis to work in apps where MongoDB is the default DB driver —
 * without requiring consumers to install MongoDB themselves.
 *
 * Priority: MongoDB\Laravel (newer) → Jenssegers\Mongodb (legacy) → standard Eloquent
 */
if (class_exists(\MongoDB\Laravel\Eloquent\Model::class)) {

    abstract class OxalisModelBase extends \MongoDB\Laravel\Eloquent\Model
    {
        public function getConnectionName(): string
        {
            return config('oxalis.connection') ?? $this->connection ?? config('database.default');
        }
    }

} elseif (class_exists(\Jenssegers\Mongodb\Eloquent\Model::class)) {

    abstract class OxalisModelBase extends \Jenssegers\Mongodb\Eloquent\Model
    {
        public function getConnectionName(): string
        {
            return config('oxalis.connection') ?? $this->connection ?? config('database.default');
        }
    }

} else {

    abstract class OxalisModelBase extends EloquentModel
    {
        public function getConnectionName(): string
        {
            return config('oxalis.connection') ?? $this->connection ?? config('database.default');
        }
    }

}

abstract class OxalisModel extends OxalisModelBase {}
