document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("get-form").addEventListener("submit", function(event) {
        event.preventDefault();

        const name = document.getElementById('get-name').value;
        const url = `index.php?name=${encodeURIComponent(name)}`;

        const xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById('get-result').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    });

    document.getElementById("post-form").addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = new FormData(this);

        fetch("post-page.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById("post-result").innerHTML = data;
        })
        .catch(error => {
            alert("Error: " + error.message);
        });
    });
});