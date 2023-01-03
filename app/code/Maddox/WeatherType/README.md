The Maddox\WeatherType module adds a new product attribute 'weather_type' to your products and creates the new REST API endpoint maddoxWeatherTypePostRepositoryV1.

maddoxWeatherTypePostRepositoryV1:
GET /V1/products-by-weather/city/{cityName}/size/{pageSize}
cityName and pageSize parameters are required.

First of all you need to include this attribute to your attribute set, and then you'll have the ability to set any type of weather to products.

Applying weather types to products - you manage what products will return in the custom endpoint, depending on the weather in the customer's city.

In the admin panel you'll see also new configurations for this module.

Forecast API section - here you have fields to set credentials for the weather API. The module use openweathermap service. So, the service works to return valid forecast information.

The weather type grid - is provided to you for managing weather types and their temperature scope. So, in other words, products will have weather type values by temperature.
For example, the cool weather type has a minimum of 5 °C and a maximum of 14 °C. If a product has the cool value for the weather type attribute, a product will display for customers that have from 5 °C to 14 °C in their city.

The module has its own log file - /var/log/weatherType.log

If you have wishes or comments write to me: roman.kolisnyk2001@gmail.com
