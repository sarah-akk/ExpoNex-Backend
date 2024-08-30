<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $atters['title'] }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        .containerParent {
            max-width: 780px;
            margin: auto;
            font-family: "Lucida Sans", "Lucida Sans Regular", "Lucida Grande",
                "Lucida Sans Unicode", Geneva, Verdana, sans-serif;
            padding: 3rem;
        }

        h1 {
            margin-bottom: 1.25rem;
            font-weight: bold;
            font-size: 1.875rem;
            line-height: 2.25rem;
        }

        .container {
            padding: 0rem 0rem 2rem 0rem;
            text-align: center;
        }

        a {
            color: #18181b;
            transition: 0.3s all;
            text-decoration: none;
        }

        a:hover {
            color: goldenrod;
        }
    </style>
</head>

<body>
    <div class="containerParent">
        <table style="margin: auto">
            <thead>
                <tr>
                    <td>
                        <h1>{{ $atters['main_header'] }},</h1>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="container">
                            <div style="text-align: left">
                                <h2>{{ $atters['sub_header'] }}</h2>
                                <p>
                                    {!! $atters['body'] !!}
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="container" style="text-align: left">
                            Sincerely,<br />
                            ExpoNex Team.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="text-align: center; color: #18181b; margin-top: 2rem">
                            <span><a href="">FAQs</a>&nbsp;|&nbsp;</span>
                            <span><a href="">Terms and Conditions</a>&nbsp;|&nbsp;</span>
                            <span><a href="">Contact Us</a></span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
