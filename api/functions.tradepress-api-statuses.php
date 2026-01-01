<?php
/**
 * Twitch API Helix API statuses.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TradePress
 * @version     1.0.0
 */

/**
* Get HTTP Status codes array or the type for a giving code.
* 
* @link https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
* 
* @version 1.0
*/
function TradePress_helix_httpstatus_groups( $status = null ) {
    $group_meanings = array(
        '1' => __( 'Informational responses.', 'tradepress' ), 
        '2' => __( 'Success.', 'tradepress' ), 
        '3' => __( 'Redirection.', 'tradepress' ), 
        '4' => __( 'Client errors.', 'tradepress' ), 
        '5' => __( 'Server errors.', 'tradepress' ), 
    );
    
    if( !$status ) { return $group_meanings; }
    
    $group_number = substr( $status, 1);
    
    if( !is_numeric( $group_number ) ) 
    {
        return __( 'Group-number must be numeric.', 'tradepress' );
    }    
    elseif( !isset( $group_meanings[ $group_number ] ) ) 
    {
        return __( 'Invalid group number returned by substr().' );
    }
    
    return $group_meanings[ $group_number ];
}

/**
* A list of HTTP status with default meaning taking from Wikipedia
* and where possible there are other interpretations to explain 
* a Twitch API (helix) result within the context of helix.
* 
* @link https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
* 
* @version 2.2
*/
function TradePress_helix_httpstatuses( $requested_status = null, $requested_meaning = 'short' ) {
    $httpstatus = array();
    
    $httpstatus[100]['short'] = __( 'Continue', 'tradepress' );     
    $httpstatus[100]['wiki'] = __( "The server has received the request headers and the client should proceed to send the request body (in the case of a request for which a body needs to be sent; for example, a POST request). Sending a large request body to a server after a request has been rejected for inappropriate headers would be inefficient. To have a server check the request's headers, a client must send Expect: 100-continue as a header in its initial request and receive a 100 Continue status code in response before sending the body. The response 417 Expectation Failed indicates the request should not be continued.", 'tradepress' );
    
    $httpstatus[101]['short'] = __( 'Switching Protocols', 'tradepress' );
    $httpstatus[101]['wiki'] = __( "The requester has asked the server to switch protocols and the server has agreed to do so.", 'tradepress' );
    
    $httpstatus[102]['short'] = __( 'Processing', 'tradepress' );
    $httpstatus[102]['wiki'] = __( "A WebDAV request may contain many sub-requests involving file operations, requiring a long time to complete the request. This code indicates that the server has received and is processing the request, but no response is available yet. This prevents the client from timing out and assuming the request was lost.", 'tradepress' );
    
    $httpstatus[103]['short'] = __( 'Checkpoint', 'tradepress' );
    $httpstatus[103]['wiki'] = __( "Used in the resumable requests proposal to resume aborted PUT or POST requests.", 'tradepress' );
    
    $httpstatus[200]['short'] = __( 'OK', 'tradepress' );
    $httpstatus[200]['wiki'] = __( "Standard response for successful HTTP requests. The actual response will depend on the request method used. In a GET request, the response will contain an entity corresponding to the requested resource. In a POST request, the response will contain an entity describing or containing the result of the action.", 'tradepress' );
    
    $httpstatus[201]['short'] = __( 'Created', 'tradepress' );
    $httpstatus[201]['wiki'] = __( "The request has been fulfilled, resulting in the creation of a new resource.", 'tradepress' );
    
    $httpstatus[202]['short'] = __( 'Accepted', 'tradepress' );
    $httpstatus[202]['wiki'] = __( "The request has been accepted for processing, but the processing has not been completed. The request might or might not be eventually acted upon, and may be disallowed when processing occurs.", 'tradepress' );
    
    $httpstatus[203]['short'] = __( 'Non-Authoritative Information', 'tradepress' );
    $httpstatus[203]['wiki'] = __( "The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin, but is returning a modified version of the origin's response.", 'tradepress' );
    
    $httpstatus[204]['short'] = __( 'No Content', 'tradepress' );
    $httpstatus[204]['wiki'] = __( "The server successfully processed the request and is not returning any content.", 'tradepress' );
    
    $httpstatus[205]['short'] = __( 'Reset Content', 'tradepress' );
    $httpstatus[205]['wiki'] = __( "The server successfully processed the request, but is not returning any content. Unlike a 204 response, this response requires that the requester reset the document view.", 'tradepress' );
    
    $httpstatus[206]['short'] = __( 'Partial Content', 'tradepress' );
    $httpstatus[206]['wiki'] = __( "The server is delivering only part of the resource (byte serving) due to a range header sent by the client. The range header is used by HTTP clients to enable resuming of interrupted downloads, or split a download into multiple simultaneous streams.", 'tradepress' );
    
    $httpstatus[207]['short'] = __( 'Multi-Status', 'tradepress' );
    $httpstatus[207]['wiki'] = __( "The message body that follows is an XML message and can contain a number of separate response codes, depending on how many sub-requests were made.", 'tradepress' );
    
    $httpstatus[208]['short'] = __( 'Already Reported', 'tradepress' );
    $httpstatus[208]['wiki'] = __( "The members of a DAV binding have already been enumerated in a preceding part of the (multistatus) response, and are not being included again.", 'tradepress' );
    
    $httpstatus[226]['short'] = __( 'IM Used', 'tradepress' );
    $httpstatus[226]['wiki'] = __( "The server has fulfilled a request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance.", 'tradepress' );
    
    $httpstatus[300]['short'] = __( 'Multiple Choices', 'tradepress' );
    $httpstatus[300]['wiki'] = __( "Indicates multiple options for the resource from which the client may choose (via agent-driven content negotiation). For example, this code could be used to present multiple video format options, to list files with different filename extensions, or to suggest word-sense disambiguation.", 'tradepress' );
    
    $httpstatus[301]['short'] = __( 'Moved Permanently', 'tradepress' );
    $httpstatus[301]['wiki'] = __( "This and all future requests should be directed to the given URI.", 'tradepress' );
    
    $httpstatus[302]['short'] = __( 'Found', 'tradepress' );
    $httpstatus[302]['wiki'] = __( "This is an example of industry practice contradicting the standard. The HTTP/1.0 specification (RFC 1945) required the client to perform a temporary redirect (the original describing phrase was \"Moved Temporarily\"), but popular browsers implemented 302 with the functionality of a 303 See Other. Therefore, HTTP/1.1 added status codes 303 and 307 to distinguish between the two behaviours. However, some Web applications and frameworks use the 302 status code as if it were the 303.", 'tradepress' );
    
    $httpstatus[303]['short'] = __( 'See Other', 'tradepress' );
    $httpstatus[303]['wiki'] = __( "The response to the request can be found under another URI using a GET method. When received in response to a POST (or PUT/DELETE), the client should presume that the server has received the data and should issue a redirect with a separate GET message.", 'tradepress' );
    
    $httpstatus[304]['short'] = __( 'Not Modified', 'tradepress' );
    $httpstatus[304]['wiki'] = __( "Indicates that the resource has not been modified since the version specified by the request headers If-Modified-Since or If-None-Match. In such case, there is no need to retransmit the resource since the client still has a previously-downloaded copy.", 'tradepress' );
    
    $httpstatus[305]['short'] = __( 'Use Proxy', 'tradepress' );
    $httpstatus[305]['wiki'] = __( "The requested resource is available only through a proxy, the address for which is provided in the response. Many HTTP clients (such as Mozilla[25] and Internet Explorer) do not correctly handle responses with this status code, primarily for security reasons.", 'tradepress' );
    
    $httpstatus[306]['short'] = __( 'Switch Proxy', 'tradepress' );
    $httpstatus[306]['wiki'] = __( "No longer used. Originally meant subsequent requests should use the specified proxy.", 'tradepress' );
    
    $httpstatus[307]['short'] = __( 'Temporary Redirect', 'tradepress' );
    $httpstatus[307]['wiki'] = __( "In this case, the request should be repeated with another URI; however, future requests should still use the original URI. In contrast to how 302 was historically implemented, the request method is not allowed to be changed when reissuing the original request. For example, a POST request should be repeated using another POST request.", 'tradepress' );
    
    $httpstatus[308]['short'] = __( 'Permanent Redirect', 'tradepress' );
    $httpstatus[308]['wiki'] = __( "The request and all future requests should be repeated using another URI. 307 and 308 parallel the behaviors of 302 and 301, but do not allow the HTTP method to change. So, for example, submitting a form to a permanently redirected resource may continue smoothly.", 'tradepress' );
    
    $httpstatus[404]['short'] = __( 'error on Wikipedia', 'tradepress' );
    $httpstatus[404]['wiki'] = __( "This class of status code is intended for situations in which the error seems to have been caused by the client. Except when responding to a HEAD request, the server should include an entity containing an explanation of the error situation, and whether it is a temporary or permanent condition. These status codes are applicable to any request method. User agents should display any included entity to the user.", 'tradepress' );
    
    $httpstatus[400]['short'] = __( 'Bad Request', 'tradepress' );
    $httpstatus[400]['wiki'] = __( "Request Not Valid. Something is wrong with the request due to an apparent client error e.g. malformed request syntax, size too large, invalid request message framing, or deceptive request routing.", 'tradepress' );
    
    $httpstatus[401]['short'] = __( 'Unauthorized', 'tradepress' );
    $httpstatus[401]['wiki'] = __( "The OAuth token does not have the correct scope or does not have the required permission on behalf of the specified user.", 'tradepress' );
    
    $httpstatus[402]['short'] = __( 'Payment Required', 'tradepress' );
    $httpstatus[402]['wiki'] = __( "Reserved for future use. The original intention was that this code might be used as part of some form of digital cash or micropayment scheme, as proposed for example by GNU Taler, but that has not yet happened, and this code is not usually used. Google Developers API uses this status if a particular developer has exceeded the daily limit on requests.", 'tradepress' );
    
    $httpstatus[403]['short'] = __( 'Forbidden', 'tradepress' );
    $httpstatus[403]['wiki'] = __( "Forbidden. This usually indicates that authentication was provided, but the authenticated user is not permitted to perform the requested operation. For example, a user who is not a partner might have tried to start a commercial.", 'tradepress' );
    
    $httpstatus[404]['short'] = __( 'Not Found', 'tradepress' );
    $httpstatus[404]['wiki'] = __( "The requested resource could not be found but may be available in the future. Subsequent requests by the client are permissible. For example, the channel, user, or relationship could not be found.", 'tradepress' );
    
    $httpstatus[405]['short'] = __( 'Method Not Allowed', 'tradepress' );
    $httpstatus[405]['wiki'] = __( "A request method is not supported for the requested resource; for example, a GET request on a form that requires data to be presented via POST, or a PUT request on a read-only resource.", 'tradepress' );
    
    $httpstatus[406]['short'] = __( 'Not Acceptable', 'tradepress' );
    $httpstatus[406]['wiki'] = __( "The requested resource is capable of generating only content not acceptable according to the Accept headers sent in the request. See Content negotiation.", 'tradepress' );
    
    $httpstatus[407]['short'] = __( 'Proxy Authentication Required', 'tradepress' );
    $httpstatus[407]['wiki'] = __( "The client must first authenticate itself with the proxy.", 'tradepress' );
    
    $httpstatus[408]['short'] = __( 'Request Timeout', 'tradepress' );
    $httpstatus[408]['wiki'] = __( "The server timed out waiting for the request. According to HTTP specifications: The client did not produce a request within the time that the server was prepared to wait. The client MAY repeat the request without modifications at any later time.", 'tradepress' );
    
    $httpstatus[410]['short'] = __( 'Gone', 'tradepress' );
    $httpstatus[410]['wiki'] = __( "Indicates that the resource requested is no longer available and will not be available again. This should be used when a resource has been intentionally removed and the resource should be purged. Upon receiving a 410 status code, the client should not request the resource in the future. Clients such as search engines should remove the resource from their indices. Most use cases do not require clients and search engines to purge the resource, and a \"404 Not Found\" may be used instead.", 'tradepress' );
    
    $httpstatus[411]['short'] = __( 'Length Required', 'tradepress' );
    $httpstatus[411]['wiki'] = __( "The request did not specify the length of its content, which is required by the requested resource.", 'tradepress' );
    
    $httpstatus[412]['short'] = __( 'Precondition Failed', 'tradepress' );
    $httpstatus[412]['wiki'] = __( "The server does not meet one of the preconditions that the requester put on the request.", 'tradepress' );
    
    $httpstatus[413]['short'] = __( 'Payload Too Large', 'tradepress' );
    $httpstatus[413]['wiki'] = __( "The request is larger than the server is willing or able to process. Previously called \"Request Entity Too Large\".", 'tradepress' );
    
    $httpstatus[414]['short'] = __( 'URI Too Long', 'tradepress' );
    $httpstatus[414]['wiki'] = __( "The URI provided was too long for the server to process. Often the result of too much data being encoded as a query-string of a GET request, in which case it should be converted to a POST request. Called \"Request-URI Too Long\" previously.", 'tradepress' );
    
    $httpstatus[415]['short'] = __( 'Unsupported Media Type', 'tradepress' ); 
    $httpstatus[415]['wiki'] = __( "The request entity has a media type which the server or resource does not support. For example, the client uploads an image as image/svg+xml, but the server requires that images use a different format.", 'tradepress' );
    
    $httpstatus[416]['short'] = __( 'Range Not Satisfiable', 'tradepress' );
    $httpstatus[416]['wiki'] = __( "The client has asked for a portion of the file (byte serving), but the server cannot supply that portion. For example, if the client asked for a part of the file that lies beyond the end of the file.[46] Called \"Requested Range Not Satisfiable\" previously.", 'tradepress' );
    
    $httpstatus[417]['short'] = __( 'Expectation Failed', 'tradepress' );
    $httpstatus[417]['wiki'] = __( "The server cannot meet the requirements of the Expect request-header field.", 'tradepress' );
    
    $httpstatus[418]['short'] = __( 'I\'m a teapot', 'tradepress' );
    $httpstatus[418]['wiki'] = __( "This code was defined in 1998 as one of the traditional IETF April Fools' jokes, in RFC 2324, Hyper Text Coffee Pot Control Protocol, and is not expected to be implemented by actual HTTP servers. The RFC specifies this code should be returned by teapots requested to brew coffee. This HTTP status is used as an Easter egg in some websites, including Google.com.", 'tradepress' );
    
    $httpstatus[421]['short'] = __( 'Misdirected Request', 'tradepress' );
    $httpstatus[421]['wiki'] = __( "The request was directed at a server that is not able to produce a response. (for example because of a connection reuse)", 'tradepress' );
    
    $httpstatus[422]['short'] = __( 'Unprocessable Entity', 'tradepress' );
    $httpstatus[422]['wiki'] = __( "For example, for a user subscription endpoint, the specified channel does not have a subscription program.", 'tradepress' );
    
    $httpstatus[423]['short'] = __( 'Locked', 'tradepress' );
    $httpstatus[423]['wiki'] = __( "The resource that is being accessed is locked.", 'tradepress' );
    
    $httpstatus[424]['short'] = __( 'Failed Dependency', 'tradepress' );
    $httpstatus[424]['wiki'] = __( "The request failed due to failure of a previous request (e.g., a PROPPATCH).", 'tradepress' );
    
    $httpstatus[426]['short'] = __( 'Upgrade Required', 'tradepress' );
    $httpstatus[426]['wiki'] = __( "The client should switch to a different protocol such as TLS/1.0, given in the Upgrade header field.", 'tradepress' );
    
    $httpstatus[428]['short'] = __( 'Precondition Required', 'tradepress' );
    $httpstatus[428]['wiki'] = __( "The origin server requires the request to be conditional. Intended to prevent the 'lost update' problem, where a client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has modified the state on the server, leading to a conflict.", 'tradepress' );
    
    $httpstatus[429]['short'] = __( 'Too Many Requests', 'tradepress' );
    $httpstatus[429]['wiki'] = __( "The user has sent too many requests in a given amount of time. Improve rate limiting for the causing feature.", 'tradepress' );
    
    $httpstatus[431]['short'] = __( 'Request Header Fields Too Large', 'tradepress' );
    $httpstatus[431]['wiki'] = __( "The server is unwilling to process the request because either an individual header field, or all the header fields collectively, are too large.", 'tradepress' );
    
    $httpstatus[451]['short'] = __( 'Unavailable For Legal Reasons', 'tradepress' );
    $httpstatus[451]['wiki'] = __( "A server operator has received a legal demand to deny access to a resource or to a set of resources that includes the requested resource.[55] The code 451 was chosen as a reference to the novel Fahrenheit 451.", 'tradepress' );
    
    $httpstatus[420]['short'] = __( 'Method Failure (Spring Framework)', 'tradepress' );
    $httpstatus[420]['wiki'] = __( "A deprecated response used by the Spring Framework when a method has failed.", 'tradepress' );
    
    $httpstatus[440]['short'] = __( 'Login Time-out', 'tradepress' );
    $httpstatus[440]['wiki'] = __( "The client's session has expired and must log in again.", 'tradepress' );
    
    $httpstatus[449]['short'] = __( 'Retry With ', 'tradepress' );
    $httpstatus[449]['wiki'] = __( "The server cannot honour the request because the user has not provided the required information.", 'tradepress' );
    
    $httpstatus[451]['short'] = __( 'Redirect', 'tradepress' );
    $httpstatus[451]['wiki'] = __( "Used in Exchange ActiveSync when either a more efficient server is available or the server cannot access the users' mailbox. The client is expected to re-run the HTTP AutoDiscover operation to find a more appropriate server.", 'tradepress' );
    
    $httpstatus[444]['short'] = __( 'No Response', 'tradepress' );
    $httpstatus[444]['wiki'] = __( "Used to indicate that the server has returned no information to the client and closed the connection.", 'tradepress' );
    
    $httpstatus[495]['short'] = __( 'SSL Certificate Error', 'tradepress' );
    $httpstatus[495]['wiki'] = __( "An expansion of the 400 Bad Request response code, used when the client has provided an invalid client certificate.", 'tradepress' );
    
    $httpstatus[496]['short'] = __( 'SSL Certificate Required', 'tradepress' );
    $httpstatus[496]['wiki'] = __( "An expansion of the 400 Bad Request response code, used when a client certificate is required but not provided.", 'tradepress' );
    
    $httpstatus[497]['short'] = __( 'HTTP Request Sent to HTTPS Port', 'tradepress' );
    $httpstatus[497]['wiki'] = __( "An expansion of the 400 Bad Request response code, used when the client has made a HTTP request to a port listening for HTTPS requests.", 'tradepress' );
    
    $httpstatus[498]['short'] = __( 'Invalid Token (Esri)', 'tradepress' );
    $httpstatus[498]['wiki'] = __( "Returned by ArcGIS for Server. Code 498 indicates an expired or otherwise invalid token.", 'tradepress' );
    
    $httpstatus[499]['short'] = __( 'Client Closed Request', 'tradepress' );
    $httpstatus[499]['wiki'] = __( "Used when the client has closed the request before the server could send a response.", 'tradepress' );

    $httpstatus[500]['short'] = __( 'Internal Server Error', 'tradepress' );
    $httpstatus[500]['wiki'] = __( "A generic error message, given when an unexpected condition was encountered and no more specific message is suitable.", 'tradepress' );
    
    $httpstatus[501]['short'] = __( 'Not Implemented', 'tradepress' );
    $httpstatus[501]['wiki'] = __( "The server either does not recognize the request method, or it lacks the ability to fulfil the request. Usually this implies future availability (e.g., a new feature of a web-service API).", 'tradepress' );
    
    $httpstatus[502]['short'] = __( 'Bad Gateway', 'tradepress' );
    $httpstatus[502]['wiki'] = __( "The server was acting as a gateway or proxy and received an invalid response from the upstream server.", 'tradepress' );
    
    $httpstatus[503]['short'] = __( 'Service Unavailable', 'tradepress' );
    $httpstatus[503]['wiki'] = __( "For example, the status of a game or ingest server cannot be retrieved.", 'tradepress' );
    
    $httpstatus[504]['short'] = __( 'Gateway Timeout', 'tradepress' );
    $httpstatus[504]['wiki'] = __( "The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.", 'tradepress' );
    
    $httpstatus[505]['short'] = __( 'HTTP Version Not Supported', 'tradepress' );
    $httpstatus[505]['wiki'] = __( "The server does not support the HTTP protocol version used in the request.", 'tradepress' );
   
    $httpstatus[506]['short'] = __( 'Variant Also Negotiates', 'tradepress' );
    $httpstatus[506]['wiki'] = __( "Transparent content negotiation for the request results in a circular reference.", 'tradepress' );
    
    $httpstatus[507]['short'] = __( 'Insufficient Storage', 'tradepress' );
    $httpstatus[507]['wiki'] = __( "The server is unable to store the representation needed to complete the request.", 'tradepress' );
    
    $httpstatus[508]['short'] = __( 'Loop Detected', 'tradepress' );
    $httpstatus[508]['wiki'] = __( "The server detected an infinite loop while processing the request (sent in lieu of 208 Already Reported).", 'tradepress' );
    
    $httpstatus[509]['short'] = __( 'Bandwidth Limit Exceeded (Apache Web Server/cPanel)', 'tradepress' );
    $httpstatus[509]['wiki'] = __( "The server has exceeded the bandwidth specified by the server administrator; this is often used by shared hosting providers to limit the bandwidth of customers.", 'tradepress' );
        
    $httpstatus[510]['short'] = __( 'Not Extended', 'tradepress' );
    $httpstatus[510]['wiki'] = __( "Further extensions to the request are required for the server to fulfil it.", 'tradepress' );
    
    $httpstatus[511]['short'] = __( 'Network Authentication Required (RFC 6585)', 'tradepress' );
    $httpstatus[511]['wiki'] = __( "The client needs to authenticate to gain network access. Intended for use by intercepting proxies used to control access to the network (e.g., \"captive portals\" used to require agreement to Terms of Service before granting full Internet access via a Wi-Fi hotspot).", 'tradepress' );

    $httpstatus[520]['short'] = __( 'Unknown Error', 'tradepress' );
    $httpstatus[520]['wiki'] = __( "The 520 error is used as a \"catch-all response for when the origin server returns something unexpected\", listing connection resets, large headers, and empty or invalid responses as common triggers.", 'tradepress' );
    
    $httpstatus[521]['short'] = __( 'Web Server Is Down', 'tradepress' );
    $httpstatus[521]['wiki'] = __( "The origin server has refused the connection from Cloudflare.", 'tradepress' );
    
    $httpstatus[522]['short'] = __( 'Connection Timed Out', 'tradepress' );
    $httpstatus[522]['wiki'] = __( "Cloudflare could not negotiate a TCP handshake with the origin server.", 'tradepress' );
    
    $httpstatus[523]['short'] = __( 'Origin Is Unreachable', 'tradepress' );
    $httpstatus[523]['wiki'] = __( "Cloudflare could not reach the origin server; for example, if the DNS records for the origin server are incorrect.", 'tradepress' );
    
    $httpstatus[524]['short'] = __( 'A Timeout Occurred', 'tradepress' );
    $httpstatus[524]['wiki'] = __( "Cloudflare was able to complete a TCP connection to the origin server, but did not receive a timely HTTP response.", 'tradepress' );
    
    $httpstatus[525]['short'] = __( 'SSL Handshake Failed', 'tradepress' ); 
    $httpstatus[525]['wiki'] = __( "Cloudflare could not negotiate a SSL/TLS handshake with the origin server.", 'tradepress' );
    
    $httpstatus[526]['short'] = __( 'Invalid SSL Certificate', 'tradepress' );
    $httpstatus[526]['wiki'] = __( "Cloudflare could not validate the SSL/TLS certificate that the origin server presented.", 'tradepress' );
    
    $httpstatus[527]['short'] = __( 'Railgun Error', 'tradepress' );
    $httpstatus[527]['wiki'] = __( "Error 527 indicates that the request timed out or failed after the WAN connection had been established.", 'tradepress' );
    
    $httpstatus[530]['short'] = __( 'Site is frozen', 'tradepress' );
    $httpstatus[530]['wiki'] = __( "Used by the Pantheon web platform to indicate a site that has been frozen due to inactivity.", 'tradepress' );

    $httpstatus[598]['short'] = __( 'Network read timeout error', 'tradepress' );
    $httpstatus[598]['wiki'] = __( "Used by some HTTP proxies to signal a network read timeout behind the proxy to a client in front of the proxy.", 'tradepress' );
      
    if( !isset( $httpstatus[ $requested_status ] ) ) 
    {
        ### error
        // __( 'Code does not match any known HTTP status code.', 'tradepress' );    
        return false;
    }   
    elseif( !isset( $httpstatus[ $requested_status ][ $requested_meaning ] ) )
    {
        // Attempt to get another meaning and reduce errors. 
        if( $requested_meaning == 'wiki' && isset( $httpstatus[ $requested_status ][ 'wiki' ] ) )
        {
            return $httpstatus[ $requested_status ][ 'wiki' ]; 
        }
        elseif( $requested_meaning == 'twitch' && isset( $httpstatus[ $requested_status ][ 'twitch' ] ) )
        {
            return $httpstatus[ $requested_status ][ 'twitch' ];
        }
    
        return __( 'The request status description does not exist.', 'tradepress' );        
    }

    return $httpstatus[ $requested_status ][ $requested_meaning ];
}
