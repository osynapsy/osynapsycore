<?php
namespace Osynapsy\Core\Auth\Jwt;

/**
 * Description of Decoder
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class Decoder
{
    public function decodeToken($secretKey, $token)
    {
        $this->validateTokenRaw($token);
        $tokenParts = explode('.', $token);
        $this->validateTokenParts($tokenParts);
        //Last part of token is the sign of token
        $recievedSignature = $tokenParts[2];
        //Part one and part two form the payload
    	$recievedHeaderAndPayload = $tokenParts[0] . '.' . $tokenParts[1];
        //Sign part one and part two with secret key
        $resultedSignature = $this->calculateSignature($secretKey, $recievedHeaderAndPayload);
        //Token is not valid if received signature is not equal to resulted signature
        $this->validateSignature($recievedSignature, $resultedSignature);
        //If token is valid decode the fields
        $fields = $this->decodeTokenData($tokenParts[1]);
        //Validate expiry
        $this->validateExpiry($fields);
        return $fields;
    }

    protected function decodeTokenData($fieldsPart)
    {
        return json_decode(base64_decode($fieldsPart), true);
    }

    protected function calculateSignature($secretKey, $recievedHeaderAndPayload)
    {
        return base64_encode(hash_hmac('sha256', $recievedHeaderAndPayload, $secretKey, true));
    }

    protected function validateExpiry($fields)
    {
        if (!empty($fields) && !empty($fields['Exp']) && $fields['Exp'] < time()) {
            throw new AuthenticationException('Token is expired', 401);
        }
    }

    protected function validateSignature($recievedSignature, $resultedSignature)
    {
        if ($resultedSignature !== $recievedSignature) {
            throw new AuthenticationException('Token is invalid. Received signature is not equal to resulted signature.', 401);
        }
    }

    protected function validateTokenRaw($token)
    {
        if (empty($token)) {
            throw new AuthenticationException('Token is empty.', 404);
        }
    }

    protected function validateTokenParts(array $tokenParts)
    {
        if (count($tokenParts) !== 3) {
            throw new AuthenticationException('Token is invalid. It is not composed of three parts.', 401);
        }
    }

    public function __invoke($secretKey, $token)
    {
        return $this->decodeToken($secretKey, $token);
    }
}
