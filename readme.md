![Tests](https://github.com/tampabaylaravel/meetup/workflows/Tests/badge.svg)

## Environment
We use [Laravel-Env-Sync](https://github.com/JulienTant/Laravel-Env-Sync) to
keep track of our environment variable files.

Verify `.env` is in sync with `.env.example`:

```sh
$ php artisan env:check
```

Verify `.env.example` has everything from `.env`:

```sh
$ php artisan env:check --reverse
```

View all differences between `.env` and `.env.example`:

```sh
$ php artisan env:diff
```

## Authentication
This app uses JWT for user authentication.

You'll want to make sure you have added the following to your `.env` file:
```
JWT_ISSUER=
JWT_SECRET=
JWT_EXPIRES_IN_MINUTES=
```

Both the `auth.login` and `auth.register` routes will return a valid token.  This token will need to be included as a `Bearer` token in the `Authorization` header for requests in order to access any routes protected by the `auth:api` middleware.

## Contributing
It might be worth it to setup a plugin for your editor to look at the ruleset
files.

Plugins:
- Vim: [ale](https://github.com/dense-analysis/ale)
- Sublime: [sublime-phpcs](https://packagecontrol.io/packages/Phpcs)
- PhpStorm: [Local PHP CS Fixer script](https://www.jetbrains.com/help/phpstorm/using-php-cs-fixer.html#8bdfa345)

If you don't want to do that you can always take advantage of the
pre-commit-hook as such:

```sh
$ cd /path/to/project/.git/hooks
$ ln -s ../../pre-commit-hook pre-commit
```

Note: currently the fixer cannot fix the method names (camelCase), so at best
the editor plugins can only warn you. With that in mind, I recommend using both
the editor plugin (to automatically make changes) and the pre-commit-hook. In
an ideal scenario the pre-commit-hook won't fail, but it's better to fail
locally before getting to CI.

Additionally, the `pre-commit-hook` runs the reverse check to help keep
`.env.example` up to date.
