<div align="center">
  <a href="" rel="noopener">
 <img width=200px height=200px src="https://i.imgur.com/4xJBwve.png" alt="Project logo"></a>
</div>

<div align="center"><h3>DatePoll (Backend)</h3></div>

---

<div align="center">
  <p>DatePoll ist eine Vereinsmanagement Applikation welche versucht
  Vereinsstrukturen und das generelle Vereinsleben zu digitalisieren.</p>
</div>

## 📝 Table of Contents
- [Getting Started](#getting_started)
- [Deployment](#deployment)
- [Usage](#usage)
- [Built Using](#built_using)
- [Authors](#authors)

## 🏁 Getting Started <a name = "getting_started"></a>
These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See [deployment](#deployment) for notes on how to deploy the project on a live system.

### Prerequisites
It is recommended to install all required libraries which are required by 
[Lumen](https://lumen.laravel.com/docs/5.8). Currently with version **5.8**:
```
PHP >= 7.1.3
Composer
Git
MySQL / MariaDB
OpenSSL PHP Extension
PDO PHP Extension
Mbstring PHP Extension
```


### Installing
After all libraries are installed and working, you can set up the
development environment for DatePoll.
<br><br>
Start with cloning DatePoll-Backend-PHP
```
git clone https://gitlab.com/BuergerkorpsEggenburg/datepoll-backend-php.git
```
<br></br>
Now go to the project folder and execute the following command
```
composer install
```
<br></br>
While all required composer libraries are being downloaded and installed,
you can set your environment variables in the meantime. 
Copy the *.env.example* file to your *.env file*
```
cp .env.example .env
```
Now set up your mail account and your database connection.
**Please change *APP_KEY* and *JWT_KEY* to random strings for security reasons.**

<br></br>
Everything set up? Migrate the database and start the dev server 
with the following commands
```
php artisan migrate
php -S localhost:8000 -t public
```

<br></br>
Now visit with your browser the website `http://localhost:8000`.
You should be able to see
> Running DatePoll-Backend! ( ͡° ͜ʖ ͡°) w

## 🔧 Running the tests <a name = "tests"></a>
Coming soon...

## 🎈 Usage <a name="usage"></a>
Um den ersten Benutzer anzulegen führe folgenden Befehl aus.
**Warnung, dieser Benutzer hat Administratorrechte.**
```
php artisan addadminuser
```

## 🚀 Deployment <a name = "deployment"></a>
If you want to deploy the DatePoll-Backend service please visit following
[page](https://gitlab.com/BuergerkorpsEggenburg/datepoll-backend-php/wikis/Installation) .

## ⛏️ Built Using <a name = "built_using"></a>
- [MariaDB](https://mariadb.org/) - Database
- [Laravel Lumen](https://lumen.laravel.com/) - Server Framework
- [Angular](https://angular.io/) - Web Framework
- [PHP](https://php.net) - Server Environment

## ✍️ Authors <a name = "authors"></a>
- [@Dafnik](https://gitlab.com/Dafnik)

See also the list of 
[contributors](https://gitlab.com/groups/BuergerkorpsEggenburg/-/group_members)
who participated in this project.