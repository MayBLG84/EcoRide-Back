mkdir -p config/jwt

echo "$JWT_PRIVATE_KEY" > config/jwt/private.pem
echo "$JWT_PUBLIC_KEY" > config/jwt/public.pem