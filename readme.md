# Receita Federal Hack for LARAVEL to get CPF and CNPJ information

This library allow you get CPF and CNPJ from Receita Federal do Brasil.

## Installation

Run the following command from you terminal:

 ```bash
 composer require "douglasresendemaciel/receita-federal:@dev"
 ```

or add this to require section in your composer.json file:

 ```
 "douglasresendemaciel/receita-federal"
 ```

then run ```composer update```

Once it is installed, you need to register the service provider.
Open up config/app.php and add the following to the providers key.

```php
'providers' => [
...
DouglasResende\ReceitaFederal\ReceitaFederalServiceProvider::class
...
```

## Rotes

For captcha route, selected the 'TYPE' of document = 'CPF' or 'CNPJ'

```php
...
CAPTCHA ROUTE => route( 'receita-federal.captcha', ['document' => TYPE ] )

PROCESS ROUTE CNPJ => route( 'receita-federal.processCNPJ', ['cnpj' => number, 'captcha' => value ] )
PROCESS ROUTE CPF => route( 'receita-federal.processCPF', ['cpf' => number, 'captcha' => value, 'birthday' => 'd/m/Y' ] )
...
```

## Usage

To show the captcha image, use img tag like ```<img src="{{ route( 'receita-federal.captcha', ['document' => TYPE ] ) }}" alt="captcha">```

then post the form to be processed at the routes of PROCESS ROUTES

## Author

Douglas Resende: [http://www.douglasresende.com/](http://www.douglasresende.com/)

## License

[mit]: http://www.opensource.org/licenses/mit-license.php

## Thanks

Marcos Peli: (http://www.facebook.com/pelimarcos)

## References

Code based on [https://github.com/Massa191/Consultas_CNPJ_CPF_Receita_Federal](https://github.com/Massa191/Consultas_CNPJ_CPF_Receita_Federal)
For more information read the official documentation at [https://laravel.com/docs/5.4/](https://laravel.com/docs/5.4/)