<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de votre billet</title>
</head>
<body>
    <h1>Merci pour votre inscription à l'événement !</h1>
    <p>Votre billet : {{ $ticket->ticket_number }}</p>
    <p>Statut : {{ $ticket->status }}</p>
</body>
</html>
