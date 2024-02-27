# Introduction

Header Authorization Bearer {{ WebViewClient getCookie.password }}

```bash
curl  --header "Content-Type: application/json"\
      --header "Authorization: Bearer autologin|KYVAFULuO7fDHjZ3oiCLgYGdTclmkGKLyiakSFqg" \
      --request POST \
      --data '{"product_id": "PRODUCT_001", "purchase_token": "MEUCIQCLJS_s4ia_sN06HqzeW7Wc3nhZi4......."}' \
      https://www.cybertronchain.com/wallet2/v1/google/purchase
```