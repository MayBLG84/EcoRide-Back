<?php

namespace App\Service\Mapper;

use App\DTO\UserSignupRequest;
use Symfony\Component\HttpFoundation\Request;

class SignupRequestMapper
{
    public function map(Request $request): UserSignupRequest
    {
        $dto = new UserSignupRequest();

        $dto->firstName = $request->request->get('firstName', '');
        $dto->lastName = $request->request->get('lastName', '');
        $dto->nickname = $request->request->get('nickname', '');
        $dto->telephone = $request->request->get('telephone', '');
        $dto->email = $request->request->get('email', '');
        $dto->password = $request->request->get('password', '');
        $dto->usageType = $request->request->get('usageType', '');

        $birthday = $request->request->get('birthday');

        if ($birthday) {
            $dto->birthday = new \DateTimeImmutable($birthday);
        }

        $address = $request->request->get('address');

        if ($address) {
            $dto->address = json_decode($address, true);
        }

        $dto->profilePicture = $request->files->get('photo');

        $dto->honeypot = $request->request->get('honeypot');

        $dto->turnstileToken = $request->request->get(
            'turnstileToken',
            ''
        );

        return $dto;
    }
}
