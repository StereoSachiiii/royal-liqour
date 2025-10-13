
<?php 
require_once __DIR__."/config/constants.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Age Verification | Royal Liquor</title>
<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(to right,rgb(247, 247, 247),rgb(252, 252, 252));
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        color:rgb(255, 255, 255);
        position: relative;
    }

    .error{

    }

    .blob{
        position: absolute;
        width: 100vw;
        height: 100vh;
    }

    .age-box {
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 0 40px rgba(184, 134, 11, 0.2);
        text-align: center;
        max-width: 400px;
        animation: age-box-animation 2s ease-in forwards;
    }


    /* animation */
    @keyframes age-box-animation {
        0%{
            scale: 1.1; 
    }100%{
            scale: 1;
    }
}



    h1 {
        margin-bottom: 20px;
        font-size: 1.8rem;
        color:rgb(0, 0, 0);
    }

    p {
        margin-bottom: 20px;
        font-size: 1rem;
        color: #333;
    }

    label {
        display: block;
        margin: 10px 0;
        font-size: 0.9rem;
        color: #B8860B;
    }

    input[type="checkbox"] {
        margin-right: 10px;
    }

    button {
        padding: 12px 25px;
        background-color: #B8860B;
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.3s ease;
    }

    button:hover {
        background-color: #A0760A;
        transform: scale(1.1);
    }

    @media(max-width: 500px){
        .age-box {
            width: 90%;
            padding: 20px;
        }
        h1 { font-size: 1.5rem; }
    }



</style>
</head>
<body>

<div class="error">

</div>

<div class="age-box">
    <h1>DISCOVER</h1>
    <p>You can find all the best deals and the best products that will fullfill your taste.</p>

    <form id="ageForm">
        <label>
            <input type="checkbox" id="confirmAge" required>
            I am 18 years or older
        </label>
        <label>
            <input type="checkbox" id="agreeTerms" required>
            I agree to the terms and conditions
        </label>
        <button type="submit">Enter Site</button>
    </form>
</div>

<script>
//error element
const errorElement = document.querySelector('.error')


//check if all boxes are checked
document.getElementById('ageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const ageChecked = document.getElementById('confirmAge').checked;
    const termsChecked = document.getElementById('agreeTerms').checked;
    

    if(ageChecked && termsChecked){
        window.location.href = "<?=AUTH ?>"
    } else {
        errorElement.textContent = "You have to check all the boxes first"
    }
});
</script>

</body>
</html>
