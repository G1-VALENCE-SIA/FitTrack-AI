<!DOCTYPE html>
<html>
<head>
    <title>FitTrackAI</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        .quote-box {
            background: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .author {
            color: gray;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>FitTrackAI Motivational Quotes</h1>

        @foreach($quotes as $quote)
            <div class="quote-box">
                <h3>{{ $quote->quote }}</h3>
                <p class="author">— {{ $quote->author }}</p>
            </div>
        @endforeach
    </div>

</body>
</html>