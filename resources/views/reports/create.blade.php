<!DOCTYPE html>
<html>
<head>
    <title>Submit a Report</title>
</head>
<body>
    <h1>Submit a City Issue Report</h1>

    @if ($errors->any())
        <div style="color:red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/report" enctype="multipart/form-data">
        @csrf
        <label>Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Description:</label><br>
        <textarea name="description" required></textarea><br><br>

        <label>Category:</label><br>
        <select name="category" required>
            <option>Garbage</option>
            <option>Broken Road</option>
            <option>Drainage</option>
            <option>Electricity</option>
        </select><br><br>

        <label>Location:</label><br>
        <input type="text" name="location"><br><br>

        <label>Photo:</label><br>
        <input type="file" name="photo"><br><br>

        <button type="submit">Submit Report</button>
    </form>

    <br><a href="/">← Back to all reports</a>
</body>
</html>
