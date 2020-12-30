# DatePoll-Backend-PHP
## Information
<h4>
    Get notified about updates and join us at
    <a href="https://discord.gg/Tc5kAH5zhH">
        <img src="https://img.shields.io/discord/697139052717146123?logo=discord&style=for-the-badge" alt="chat on Discord">
    </a>
</h4>

*  Project website (https://datepoll.org)
*  API / backend documentation (https://docs.datepoll.org)
*  Other projects
    * [DatePoll-Frontend](https://gitlab.com/DatePoll/DatePoll/datepoll-frontend)
    * [DatePoll-Android](https://gitlab.com/DatePoll/DatePoll/datepoll-android)
    * [DatePoll-Dockerized](https://gitlab.com/DatePoll/DatePoll/datepoll-dockerized)
* created with [Lumen](https://lumen.laravel.com) [used libaries](https://gitlab.com/DatePoll/DatePoll/datepoll-backend-php/-/blob/master/composer.json)

## Releases
Releases are managed over git branches.

There are 2 different types:
1. Latest release version: [master](https://gitlab.com/DatePoll/DatePoll/datepoll-backend-php/-/tree/master)
1. Latest dev version: [dev](https://gitlab.com/DatePoll/DatePoll/datepoll-backend-php/-/tree/development)
## Installation
Please head to [this page](https://docs.datepoll.org/DatePoll/installation/) to get the latest install instructions! Deployment and development are managed over Docker.

## DatePoll Dockerized commands cheat sheet
### Accessing composer in docker container
`docker-compose exec datepoll-php php /usr/local/bin/composer`

### Accessing artisan in docker container
`docker-compose exec datepoll-php php artisan`

### Accessing the sql server
`docker-compose exec datepoll-mysql mysql -u homestead -p`

Password: `homestead`

To use the DatePoll database: `use homestead;`
