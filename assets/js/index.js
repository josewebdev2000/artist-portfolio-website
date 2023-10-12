// My own JS script for index.html
$(document).ready(function() {
    showInfoAlert("<h3 style='color:#18d26e;'>Info</h3>", "<h6>This is a ficticious portfolio created to showcase web development skills</h6>");
    grabContentForDetailsPage(); // Portfolio Details Related Code
    sendContactInfoToServer(); // Contact Form Related Code
});

/* Custom jAlert Handlers */
function showInfoAlert(titleHTML, messageHTML)
{
    // Show info alert accoding to sessionStorage showInfo value
    const showInfo = !window.location.href.includes("#") || !window.location.href.includes("portfolio");

    // User is in raw index.html page and has not visited any section
    if (showInfo)
    {
        $.jAlert({
            "title": titleHTML,
            "content": messageHTML,
            "theme": "black",
        });
    }
}

function showErrorAlert(errorMessage)
{
    // Show an error jAlert when something went wrong
    errorAlert(errorMessage);
}

function showSuccessAlert(successMessage)
{
    // Show a success jAlert when something went right
    successAlert(successMessage);
}

function showPortfolioDetails()
{
    // Show portfolio details section
    const portfolioDetails = $("aside#pd");
    const portfolioShowCase = $("main#main");

    portfolioDetails.show();
    portfolioShowCase.hide();

    // Display results from sessionStorage
    displayStoredResults();
}

function hidePortfolioDetails()
{
    // Hide portfolio details section
    const portfolioDetails = $("aside#pd");
    const portfolioShowCase = $("main#main");

    portfolioDetails.hide();
    portfolioShowCase.show();
}

/* Portfolio Details Code */

function grabContentForDetailsPage()
{
    // Grab all div elements with a class "portfolio-links"
    const portfolioDetailsLinksContainers = $("div.portfolio-links");

    // Loop through each div that contains portfolio-links
    portfolioDetailsLinksContainers.each(function(i) {

        // Grab the current number for this container
        const currentContainerNum = i + 1;

        // Grab the current div
        const currentPortfolioDetailsLinkContainer = $(this);

        // Grab both <a> link tags which are the children of this div
        const currentPortfolioDetailsLinks = currentPortfolioDetailsLinkContainer.find("a");

        // Grab the first and second link separately
        const currentPortfolioDetailsLink = currentPortfolioDetailsLinks.eq(1);
        
        // Add a click event to the second link
        currentPortfolioDetailsLink.on("click", function(e) {
            // Do not redirect to portfolio-details.html (It does not exist anymore)
            e.preventDefault();
            
            // Form a JSON AJAX request to the server with the following information
            /*
                {
                    "type" : title of first link
                }

                Intended response is
                {
                    "title": Project Title
                    "category": Project Category
                    "company": Company/Organization
                    "date": Date of completion
                    "description": Project Description
                }
            */
           // Prevent default behaviour

           /* ONLY SEND AJAX REQUEST WHEN THE LINK DOES NOT CONTAIN A SPAN WITH THE CLASS SPINNER

           // THIS IS DONE TO AVOID SENDING UNNECESSARY REQUESTS TO THE BACK-END

           // IN CASE THE USER CLICKS THIS LINK MORE THAN ONE BEFORE RECEIVING A RESPONSE

           // FROM THE BACK END */

           if (!currentPortfolioDetailsLink.is(":has(span.spinner-border)"))
           {
            $.ajax({ // Configure AJAX Request to send to the Back End
                url: portfolioDetailsBackEndProcessorLink,
                type: "POST",
                data: JSON.stringify({
                    "id": currentContainerNum
                }),
                beforeSend: function() { 
                    /* Code to execute before sending the AJAX request */

                    // Add a spinner to signal the user a request has been sent but is not yet ready
                    currentPortfolioDetailsLink.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                },
                success: function(response)
                { 
                    /* Code to execute in case AJAX request was successful */

                    // Add image URL data to the JSON response from the server
                    let jsonResponse = response;
                    jsonResponse['image_url'] = "assets/img/portfolio/portfolio-" + currentContainerNum + ".jpg";
    
                    // Stringify the response in JSON
                    const jsonResponseStringVersion = JSON.stringify(jsonResponse);
    
                    // Set it to sessionStorage
                    sessionStorage.setItem("portfolioDetails", jsonResponseStringVersion);

                    // Display the portfolio details
                    showPortfolioDetails();
                },
                error: function(xhr, status, error)
                {
                    /* Code to execute in case AJAX request failed */
                    //console.log("AJAX request error: ", xhr, status, error);
                    // Show jAlert error to the user
                    showErrorAlert("<div><p>Couldn't get information about this portfolio project.</p><p>Try again later</p></div>");
                },
                complete: function() 
                {
                    /* Code to always execute after this AJAX request */
                    // Add the normal icon for the link after the request is done
                    currentPortfolioDetailsLink.html('<i class="bx bx-link"></i>');
                }
              });
           }
           
        });

    });
}

function displayStoredResults()
{
    // Display the results shown in session storage
    // Grab content from session storage
    const sessionStorageContent = sessionStorage.getItem("portfolioDetails");

    // Convert it into anobject
    const sessionStorageObject = JSON.parse(sessionStorageContent);

    // Grab required DOM elements
    const portfolioTitleElement = $("#portfolio-title");
    const portfolioImageElement = $("#portfolio-image");
    const portfolioCategoryElement = $("#portfolio-category");
    const portfolioCompanyElement = $("#portfolio-company");
    const portfolioDateElement = $("#portfolio-date");
    const portfolioDescriptionElement = $("#portfolio-description");

    // Set the text of each DOM element to its required property
    portfolioTitleElement.text(sessionStorageObject.title);
    portfolioImageElement.attr("src", sessionStorageObject.image_url);
    portfolioCategoryElement.text(sessionStorageObject.category);
    portfolioCompanyElement.text(sessionStorageObject.company);
    portfolioDateElement.text(sessionStorageObject.date);
    portfolioDescriptionElement.text(sessionStorageObject.description);

    // Allow user to go back to default page
    goBackToPortfolio();
}

function goBackToPortfolio()
{
    // Go back to the portfolio page when the user clicks the "x" button
    const goBackLink = $("#go-back-link");

    // Add an click event listener to this link with the on method
    goBackLink.on("click", function(e) {
        e.preventDefault();
        clearSessionStorage();
        hidePortfolioDetails();
    });
}

function clearSessionStorage()
{
    //Be sure to clear session Storage when it's no longer required
    sessionStorage.removeItem("portfolioDetails");
}

/* Contact Form Code */

function addClassIfAbsent(element, classToAdd)
{
    /* Add a class to a DOM element if it doesn't have it */
    if (!element.hasClass(classToAdd))
    {
        element.addClass(classToAdd);
    }
}

function removeClassIfPresent(element, classToRemove)
{
    /* Remove a class from an element if it has it */
    if (element.hasClass(classToRemove))
    {
        element.removeClass(classToRemove);
    }
}

function isInvalidField(conditionForInvalid, element)
{
    /* Show an element as invalid or not according to a given condition */
    if (conditionForInvalid)
    {
        addClassIfAbsent(element, "is-invalid");
        return true;
    }

    else
    {
        removeClassIfPresent(element, "is-invalid");
        return false;
    }
}

function sendContactInfoToServer()
{
    /* Send the contact info to the server */

    // Grab the contact form
    const contactForm = $("form#contact-form");
    const contactFormBtn = document.querySelector("button#contact-form-btn");
    // Listen for the submit event
    contactForm.on("submit", function(e) {
        // Prevent default event
        e.preventDefault();

        // Disable the button
        contactFormBtn.disabled = true;

        // Grab elements that hold required data
        const nameElement = $("input#name");
        const emailElement = $("input#email");
        const subjectElement = $("input#subject");
        const messageElement = $("textarea[name='message']");

        // Grab required data from elements
        const name = nameElement.val();
        const email = emailElement.val();
        const subject = subjectElement.val();
        const message = messageElement.val();

        // Regex for Form Validation
        const nameRegex = /^[A-Za-z\s]+$/;
        const emailRegex = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;

        // Check for any invalid element
        isInvalidField(!nameRegex.test(name), nameElement);
        isInvalidField(!emailRegex.test(email), emailElement);
        isInvalidField(subject.length === 0, subjectElement);
        isInvalidField(message.length === 0, messageElement);

        // Provide an AJAX request in order to submit this to the back-end
        $.ajax({
            url: contactBackEndProcessorLink,
            type: "POST",
            data: JSON.stringify({
                "name" : name,
                "email" : email,
                "subject": subject,
                "message": message
            }),
            success: function(response)
            {
                if (response.hasOwnProperty("success"))
                {
                    showSuccessAlert(response["success"]);
                }

                else if (response.hasOwnProperty("error"))
                {
                    showErrorAlert(response["error"]);
                }

                else
                {
                    showErrorAlert("Communication with the web server failed.");
                }
                // Enable the button back
                contactFormBtn.disabled = false;
            },
            error: function(xhr, status, error)
            {
                showErrorAlert("<div><p>Couldn't send your message.</p><p>Try again later</p></div>");
                // Enable the button back
                contactFormBtn.disabled = false;
            }
        });

    });

}