document.getElementById("toggleBtn").addEventListener("click", function () {
    let sidebar = document.getElementById("sidebar");
    let mainContent = document.getElementById("mainContent");
    let spans = sidebar.querySelectorAll("a span");

    sidebar.classList.toggle("collapsed");
    mainContent.classList.toggle("expanded");

    // Toggle visibility of text
    spans.forEach(span => {
        if (sidebar.classList.contains("collapsed")) {
            span.style.display = "none";
        } else {
            span.style.display = "inline";
        }
    });
});