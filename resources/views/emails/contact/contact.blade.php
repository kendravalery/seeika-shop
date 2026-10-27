<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Contact Us</title>
</head>

<body style="font-family: arial,sans-serif; background: #f3f4f6; padding:20px;">
    <div style="max-width: 600px ;margin:auto; background:white; padding:20px;border-radius:10px;">
        <h2 style="color:#333;">
            Pesan Baru Contact Us - seeika Shop
        </h2>
        <hr>
        <p><strong>Name :</strong>
            {{ $data['name'] }}
        </p>
        <p><strong>Email : </strong>
            {{ $data['email'] }}
        </p>
        <p><strong>Subject : </strong>
            {{ $data['subject'] }}
        </p>
        <p><strong>Message : </strong>
            {{ $data['message'] }}
        </p>

    </div>
</body>

</html>
