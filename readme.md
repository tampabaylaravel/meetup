## Authentication
This app uses JWT for user authentication.

You'll want to make sure you have added the following to your `.env` file:
```
JWT_ISSUER=
JWT_SECRET=
JWT_EXPIRES_IN_MINUTES=
```

Both the `auth.login` and `auth.register` routes will return a valid token.  This token will need to be included as a `Bearer` token in the `Authorization` header for requests in order to access any routes protected by the `auth:api` middleware.
