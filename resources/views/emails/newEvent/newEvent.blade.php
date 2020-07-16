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
    <h3>Hallo, {{$name}} | Event-Erinnerung</h3>
    <blockquote>Es wurd ein neues Event hinzugefügt!</blockquote>
    <p>Name: {{$eventName}}</p>
    <p>Start-Datum: {{$startDate}}</p>
    <p>End-Datum: {{$endDate}}</p>
    <p><a href="{{$DatePollAddress}}/home/events/{{$eventId}}">Klick mich um mehr zu erfahren.</a></p>
    <p><i>Willst du diese Email-Erinnerungen deaktivieren? <a href="{{$DatePollAddress}}/home/settings/emailSettings">Hier kannst du es!</a></i></p>
    <i>Dies ist eine automatisierte Nachricht. Bitte antworten Sie nicht.</i>
</div>
</body>
</html>