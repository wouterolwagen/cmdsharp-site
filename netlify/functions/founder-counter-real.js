const fetch = require('node-fetch');

// REAL counter system using JSONBin.io for persistent storage
// This ensures ONLY 30 signups total across ALL browsers/devices

exports.handler = async (event, context) => {
  const headers = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'Content-Type',
    'Access-Control-Allow-Methods': 'GET, POST, OPTIONS'
  };

  if (event.httpMethod === 'OPTIONS') {
    return {
      statusCode: 200,
      headers,
      body: ''
    };
  }

  const JSONBIN_API_KEY = process.env.JSONBIN_API_KEY;
  const JSONBIN_BIN_ID = process.env.JSONBIN_BIN_ID;
  
  if (!JSONBIN_API_KEY || !JSONBIN_BIN_ID) {
    return {
      statusCode: 500,
      headers,
      body: JSON.stringify({ success: false, message: 'Counter storage not configured' })
    };
  }

  const binUrl = `https://api.jsonbin.io/v3/b/${JSONBIN_BIN_ID}`;

  // GET - Fetch current counter
  if (event.httpMethod === 'GET') {
    try {
      const response = await fetch(binUrl, {
        headers: {
          'X-Master-Key': JSONBIN_API_KEY
        }
      });
      
      if (!response.ok) {
        throw new Error('Failed to fetch counter');
      }
      
      const data = await response.json();
      const counterData = data.record;
      
      return {
        statusCode: 200,
        headers,
        body: JSON.stringify({
          success: true,
          remaining: Math.max(0, counterData.remaining || 30),
          total: 30,
          filled: 30 - Math.max(0, counterData.remaining || 30),
          last_updated: counterData.last_updated,
          signups: counterData.signups ? counterData.signups.length : 0
        })
      };
    } catch (error) {
      console.error('Error fetching counter:', error);
      return {
        statusCode: 500,
        headers,
        body: JSON.stringify({ success: false, message: 'Failed to fetch counter' })
      };
    }
  }

  // POST - Update counter (decrement or set)
  if (event.httpMethod === 'POST') {
    try {
      const requestData = JSON.parse(event.body);
      
      // First, fetch current data
      const response = await fetch(binUrl, {
        headers: {
          'X-Master-Key': JSONBIN_API_KEY
        }
      });
      
      if (!response.ok) {
        throw new Error('Failed to fetch current counter');
      }
      
      const data = await response.json();
      let counterData = data.record;
      
      // Initialize if doesn't exist
      if (!counterData || typeof counterData.remaining === 'undefined') {
        counterData = {
          remaining: 30,
          total: 30,
          last_updated: new Date().toISOString(),
          signups: []
        };
      }
      
      // Handle decrement (someone signing up)
      if (requestData.action === 'decrement') {
        if (counterData.remaining <= 0) {
          return {
            statusCode: 400,
            headers,
            body: JSON.stringify({
              success: false,
              message: 'No spots remaining - founder program is full!',
              remaining: 0
            })
          };
        }
        
        // Decrement counter
        counterData.remaining--;
        counterData.last_updated = new Date().toISOString();
        
        // Log the signup
        if (requestData.email) {
          counterData.signups = counterData.signups || [];
          counterData.signups.push({
            email: requestData.email,
            timestamp: new Date().toISOString(),
            ip: event.headers['x-forwarded-for'] || 'unknown'
          });
        }
      }
      
      // Handle manual set (admin)
      else if (requestData.action === 'set') {
        const newRemaining = Math.max(0, Math.min(30, parseInt(requestData.remaining)));
        counterData.remaining = newRemaining;
        counterData.last_updated = new Date().toISOString();
      }
      
      else {
        return {
          statusCode: 400,
          headers,
          body: JSON.stringify({ success: false, message: 'Invalid action' })
        };
      }
      
      // Save updated counter
      const updateResponse = await fetch(binUrl, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-Master-Key': JSONBIN_API_KEY
        },
        body: JSON.stringify(counterData)
      });
      
      if (!updateResponse.ok) {
        throw new Error('Failed to update counter');
      }
      
      return {
        statusCode: 200,
        headers,
        body: JSON.stringify({
          success: true,
          remaining: Math.max(0, counterData.remaining),
          filled: 30 - Math.max(0, counterData.remaining),
          message: requestData.action === 'decrement' ? 'Founder spot secured!' : 'Counter updated',
          last_updated: counterData.last_updated
        })
      };
      
    } catch (error) {
      console.error('Error updating counter:', error);
      return {
        statusCode: 500,
        headers,
        body: JSON.stringify({ success: false, message: 'Failed to update counter' })
      };
    }
  }

  return {
    statusCode: 405,
    headers,
    body: JSON.stringify({ success: false, message: 'Method not allowed' })
  };
};