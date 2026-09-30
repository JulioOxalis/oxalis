<?php
namespace Oxalis\Models;

use Oxalis\Models\OxalisModel;

class SocialLogin extends OxalisModel
{
    protected $table = 'oxalis_social_logins';

    protected $fillable = ['user_id', 'provider', 'provider_id', 'email', 'avatar'];
}
