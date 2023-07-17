<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    this is test file 
    <form action="/upload" method="POST" enctype="multipart/form-data">
        @method('POST')
        @csrf
        <img src="{{ asset('product_img/uwn3iIPNyLBDR9qO0liRcTi1uKa0n4pYp8iQhWrc.jpg') }}" alt="NONONO">
        {{ Storage::get('uwn3iIPNyLBDR9qO0liRcTi1uKa0n4pYp8iQhWrc.jpg') }}
        <input type="file" name="avatar" id="">
        <button type="submit">upload</button>
    </form>
    
</body>
</html>