<html>
<head>
    <style type="text/css">
        a {
            color: #2196F3;
        }
        a:hover {
            text-decoration: underline;
        }
        h3 {
            margin-top: 0;
        }
        body {
            padding-left: 3vh;
            padding-right: 3vh;
            padding-top: 1vh;
            font-size: 11px;
        }
        blockquote {
            border-radius: 25px;
            padding: 20px;
            color: black;
            text-align: center;
            background-color: #E0E0E0;
        }
        .content {
            border-radius: 25px;
            padding: 20px;
            margin-bottom: 3vh;
            box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.14), 0 1px 7px 0 rgba(0, 0, 0, 0.12), 0 3px 1px -1px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="content">
        <h3>Hallo, {{$name}} | Accountaktivierung</h3>
        <blockquote>Dein DatePoll Account wurde erfolgreich aktiviert!</blockquote>
        <p>Bitte melde dich bei <a href="{{$DatePollAddress}}">DatePoll</a> mit folgenden Benutzername und Ersatzpasswort an!</p>
        <p>Benutzername: <b>{{$username}}</b></p>
        <p>Passwort für die Erstanmeldung: <b>{{$code}}</b></p>
        <small>Wir empfehlen ein eigenes Passwort für DatePoll zu benutzen.</small>
        <p><i>Sie haben keinen DatePoll Account? Ignorieren Sie diese Email einfach.</i></p>
        <i>Dies ist eine automatisierte Nachricht. Bitte antworten Sie nicht.</i>
    </div>
</body>
</html>