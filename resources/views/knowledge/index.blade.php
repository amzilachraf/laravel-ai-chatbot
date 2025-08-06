<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

  
</head>
<body>
    <div class="container py-4">
        <h5>Add Knowledge</h5>
        <a class="btn btn-secondary btn-sm" href="{{ route('chat.index') }}">Back</a>
     <form action="{{ route('knowledge.store') }}" method="POST">
        @csrf
        <textarea name="knowledge" id="knowledge" class="form-control mb-3" rows="5" placeholder="Enter knowledge information">{{ $knowledge->information ?? '' }}</textarea>
        <button class="btn btn-primary">Add Knowledge</button>
     </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
</body>
</html>