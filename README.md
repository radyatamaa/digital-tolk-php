# digital_tolk_php

Explained The Refactor Code
=================
The Weakness Old Code : 
1. There are no interface for implemented the function such as Repository, which are the codes doesn't be a clean for implemented
2. There are no Bussiness Logic Layer , bussineess logic the codes mostly implement the logic in Controller which are the code did not made a cleaned
3. The Repository didn't made a grouping base on logical goals, too much function handle on one repository (BookingRepository)
4. There are some functions have made doesn't be a clean code such as getAll(Repository) and etc. Have many if condition whereas the logic could make a simple if condition

What are changes The Code : 
1. Each the layer have an Interface such as Service Layer, Repository Layer. So the code make more be a clean and an interface we can mock later on Unit Test
2. Each Logic have a layer start from Repository Layer (for data query) -> Service Layer (for bussness logic) , Delivery Layer / Controller (for Endpoint API)
3. The Repository made a group in accordance logical goals
4. There are some function Optimized such as getAll(Repository) and etc.

What are adds The code : 
1. Added example unit test with mocking schema and with each the layer
2. Added Providers App service for Dependency injection Register Interface
