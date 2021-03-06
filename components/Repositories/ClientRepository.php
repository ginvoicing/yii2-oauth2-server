<?php

namespace chervand\yii2\oauth2\server\components\Repositories;

use chervand\yii2\oauth2\server\models\Client;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use yii\base\Component;

class ClientRepository extends Component implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity(
        $clientIdentifier,
        $grantType,
        $clientSecret = null,
        $mustValidateSecret = true
    ) {
        $clientEntity = Client::getDb()
            ->cache(function () use ($clientIdentifier, $grantType) {

                $query = Client::find();

                if ($grantType !== null) {
                    $query->grant($grantType);
                }

                return $query
                    ->active()
                    ->identifier($clientIdentifier)
                    ->one();
            });

        if (
            $clientEntity instanceof Client
            && (
                $clientEntity->getIsConfidential() !== true
                || $mustValidateSecret !== true
                || Client::secretVerify($clientSecret, $clientEntity->secret) === true
            )
        ) {
            return $clientEntity;
        }

        return null;
    }
}
