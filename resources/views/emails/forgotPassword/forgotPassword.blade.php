<html lang="de">
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
    <h3>Hallo, {{$name}}</h3>
    <p>Für deinen DatePoll-Account wurde ein Passwort Reset angefordert!</p>
    <p>Kopiere folgenden Code und füge ihn auf der Website ein.</p>
    <p>Code: <b>{{$code}}</b></p>
    <p>Sie haben keinen DatePoll Account oder keinen Passwort Reset angefordert? Ignorieren Sie diese E-Mail einfach.</p>
    <i>Dies ist eine automatisierte Nachricht. Bitte antworten Sie nicht.</i>
</div>
</body>
</html>
