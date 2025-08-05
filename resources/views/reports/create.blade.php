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

        <div class="mb-4">
            <label for="city_corporation" class="block text-sm font-medium text-gray-700">City Corporation</label>
            <select name="city_corporation" id="city_corporation" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Select a city</option>
                <option value="Dhaka North City Corporation">Dhaka North City Corporation</option>
                <option value="Dhaka South City Corporation">Dhaka South City Corporation</option>
                <option value="Chittagong City Corporation">Chittagong City Corporation</option>
                <option value="Rajshahi City Corporation">Rajshahi City Corporation</option>
                <option value="Khulna City Corporation">Khulna City Corporation</option>
                <option value="Sylhet City Corporation">Sylhet City Corporation</option>
                <option value="Barisal City Corporation">Barisal City Corporation</option>
                <option value="Rangpur City Corporation">Rangpur City Corporation</option>
                <option value="Mymensingh City Corporation">Mymensingh City Corporation</option>
                <option value="Narayanganj City Corporation">Narayanganj City Corporation</option>
                <option value="Comilla City Corporation">Comilla City Corporation</option>
                <option value="Bogura City Corporation">Bogura City Corporation</option>
            </select>
        </div>

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
