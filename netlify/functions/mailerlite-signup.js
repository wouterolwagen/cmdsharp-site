const fetch = require('node-fetch');

exports.handler = async (event, context) => {
  // CORS headers
  const headers = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'Content-Type',
    'Access-Control-Allow-Methods': 'POST, OPTIONS'
  };

  // Handle preflight requests
  if (event.httpMethod === 'OPTIONS') {
    return {
      statusCode: 200,
      headers,
      body: ''
    };
  }

  // Only allow POST requests
  if (event.httpMethod !== 'POST') {
    return {
      statusCode: 405,
      headers,
      body: JSON.stringify({ success: false, message: 'Method not allowed' })
    };
  }

  try {
    const data = JSON.parse(event.body);
    
    if (!data.email || !data.groupId) {
      return {
        statusCode: 400,
        headers,
        body: JSON.stringify({ success: false, message: 'Missing required fields' })
      };
    }

    const { email, groupId } = data;
    
    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return {
        statusCode: 400,
        headers,
        body: JSON.stringify({ success: false, message: 'Invalid email address' })
      };
    }

    // Get MailerLite API key from environment variables
    const MAILERLITE_API_KEY = process.env.MAILERLITE_API_KEY;
    if (!MAILERLITE_API_KEY) {
      return {
        statusCode: 500,
        headers,
        body: JSON.stringify({ success: false, message: 'MailerLite API key not configured' })
      };
    }

    // Prepare subscriber data
    const subscriberData = {
      email: email,
      status: 'active',
      fields: {
        signup_source: 'cmdsharp_website',
        signup_date: new Date().toISOString(),
        founder_status: groupId === 'founder-30' ? 'founder' : 'waitlist'
      }
    };

    // Create subscriber in MailerLite
    const response = await fetch('https://connect.mailerlite.com/api/subscribers', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${MAILERLITE_API_KEY}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(subscriberData)
    });

    const responseData = await response.json();

    if (response.ok) {
      const subscriberId = responseData.data?.id;

      // Add to specific group if subscriber was created and we have a subscriberId
      if (subscriberId && groupId) {
        const groupResponse = await fetch(`https://connect.mailerlite.com/api/groups/${groupId}/subscribers`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${MAILERLITE_API_KEY}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ subscriber_id: subscriberId })
        });

        if (!groupResponse.ok) {
          console.error('Failed to add subscriber to group:', await groupResponse.text());
        }
      }

      return {
        statusCode: 200,
        headers,
        body: JSON.stringify({
          success: true,
          message: `Successfully subscribed to ${groupId === 'founder-30' ? 'Founder-30' : 'Waitlist'}`,
          subscriber_id: subscriberId
        })
      };
    } else {
      console.error('MailerLite API error:', responseData);
      return {
        statusCode: response.status,
        headers,
        body: JSON.stringify({
          success: false,
          message: 'Failed to subscribe. Please try again.',
          error: responseData.message || 'Unknown error'
        })
      };
    }

  } catch (error) {
    console.error('MailerLite signup error:', error);
    return {
      statusCode: 500,
      headers,
      body: JSON.stringify({
        success: false,
        message: 'Server error. Please try again later.'
      })
    };
  }
};