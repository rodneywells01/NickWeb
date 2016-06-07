function appendResults(text) {
        var results = document.getElementById('results');
        results.appendChild(document.createElement('P'));
        results.appendChild(document.createTextNode(text));
        console.log("holy shit: " + text);
      }

function makeRequest() {
  var request = gapi.client.urlshortener.url.get({
    'shortUrl': 'http://goo.gl/fbsS'
  });
  request.then(function(response) {
    appendResults(response.result.longUrl);
  }, function(reason) {
    console.log('Error: ' + reason.result.error.message);
  });
}


var CLIENT_ID = '453516093674-kd9la3n94lm07u2h0p47blr0tec73ts5.apps.googleusercontent.com';
// var CLIENT_ID = 'a';

var SCOPES = ['https://www.googleapis.com/auth/gmail.compose'];

function sendMessage(userId, email) {
  // Send user a message.
  // https://developers.google.com/gmail/api/v1/reference/users/messages/send
  /**
 * Send Message.
 *
 * @param  {String} userId User's email address. The special value 'me'
 * can be used to indicate the authenticated user.
 * @param  {String} email RFC 5322 formatted String.
 * @param  {Function} callback Function to call when the request is complete.*/

  var base64EncodedEmail = btoa(email);
  try {
    var request = gapi.client.gmail.users.messages.send({
    'userId': userId,
    'message': {
      'raw': base64EncodedEmail
    }
    });
    console.log("EMAIL SENT!");
    console.log(request);
  } catch(err) {
    console.log("ERROR: FAILED TO SEND");
    console.log(err);
  }
  
 
}

function checkAuth() {
  gapi.auth.authorize(
    {
      'client_id': CLIENT_ID,
      'scope': SCOPES,
      'immediate': true
    }, handleAuthResult);
}

/**
 * Handle response from authorization server.
 *
 * @param {Object} authResult Authorization result.
 */
function handleAuthResult(authResult) {
  if (authResult && !authResult.error) {
    // Hide auth UI, then load client library.
    loadGmailApi();
  } else {
    
  }
}

/**
 * Initiate auth flow in response to user clicking authorize button.
 *
 * @param {Event} event Button click event.
 */
function handleAuth() {
  gapi.auth.authorize(
    {client_id: CLIENT_ID, scope: SCOPES, immediate: false},
    handleAuthResult);
  return false;
}

/**
 * Load Gmail API client library. List labels once client library
 * is loaded.
 */
function loadGmailApi() {
  gapi.client.setApiKey('AIzaSyA5d9KwNG-4h5BbbWsiLxeUnMlj1El8k84');
  gapi.client.load('gmail', 'v1');
  console.log("GAPI LOADED");
}
