<html lang="de">
<body>
<div><a href="mailto:{{ $emailAddress }}?subject=Re: {{ $mSubject }}">Klick mich um zu antworten.</a></div>

<div> {!! $bodyHTML !!} </div>

<div> {!! $mAttachments !!} </div>

<small><i>Diese Email wurde über Ihr <a href="{{ $DatePollAddress }}" target="_blank" rel="noopener">DatePoll</a>-Verteiler
        System gesendet!<br>Bitte antworten Sie nicht.</i></small>
</body>
</html>
