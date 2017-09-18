
Scenario 0: Preconditions

Verify that the following room types exist
id | name                                  | type      | number of beds | 
35 | 6 bed Dorm                            | DORM      | 6              | 
39 | Double room with shared bathroom      | PRIVATE   | 2              | 
42 | 10 bed Dorm                           | DORM      | 10             | 
46 | Double room ensuite                   | PRIVATE   | 2              | 
69 | Deluxe Studio Apartment, Ferenciek    | APARTMENT | 4              | 
70 | Studio Apartment, Ferenciek           | APARTMENT | 2              | 
72 | One-Bedroomm Apartment 2.0, Ferenciek | APARTMENT | 5              | 

Verify that the following rooms exist
id | name                     | room type id | room type name                         |
40 | 11. Lemon                | 39           | Double room with shared bathroom       |
39 | 12. Yellow               | 39           | Double room with shared bathroom       |
35 | 13. The Blue Brothers    | 35           | 6 bed Dorm                             |
36 | 14. Mss Peach            | 35           | 6 bed Dorm                             |
42 | 15. Mr Green             | 42           | 10 bed dorm                            |
46 | 16. 4.em. Mia            | 46           | Double room ensuite                    |
48 | 18. 4.em. Jules          | 46           | Double room ensuite                    |
49 | 17. 4.em. Vincent        | 46           | Double room ensuite                    |
50 | 19. 4.em. Butch          | 46           | Double room ensuite                    |
51 | 20. 4.em. Honey          | 46           | Double room ensuite                    |
80 | 21. Nathan               | 69           | Deluxe Studio Apartment, Ferenciek     |
82 | 22. Simon                | 70           | Studio Apartment, Ferenciek            |
79 | 23. Kelly                | 72           | One-Bedroom Apartment 2.0, Ferenciek   |
81 | 24. Curtis               | 69           | Deluxe Studio Apartment, Ferenciek     |
78 | 25. Alisha               | 69           | Deluxe Studio Apartment, Ferenciek     |




Scenario 1: Extract some bookings into file
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no prices set
Start date | end date
2010-01-01 | 2010-12-31

Given there is no availability extracted

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 |
11. Lemon                | 39 Double room with shared bathroom     |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |   4        |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |            |    X       |
19. 4.em. Butch          | 46 Double room ensuite                  |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |
22. Simon                | 70 Studio Apartment, Ferenciek          |    X       |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |    X       |

When I extract the availability into a file
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following bookings will be saved in the file
room name               | type | number of person | date       |
13. The Blue Brothers   | BED  |    4             | 2010-01-02 |
17. 4.em. Vincent       | ROOM |    2             | 2010-01-03 |
21. Nathan              | ROOM |    4             | 2010-01-02 |
21. Nathan              | ROOM |    4             | 2010-01-03 |
22. Simon               | ROOM |    2             | 2010-01-02 |
25. Alisha              | ROOM |    4             | 2010-01-03 |





Scenario 2: Extract some multi-day bookings into file
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no prices set
Start date | end date
2010-01-01 | 2010-12-31

Given there is no availability extracted

Given the following multi day bookings exist
room name                | room type                               | first night | last night | number of person |
13. The Blue Brothers    | 35 6 bed Dorm                           | 2010-01-02  | 2010-01-05 |       4          | 
18. 4.em. Jules          | 46 Double room ensuite                  | 2010-01-02  | 2010-01-06 |       2          |
17. 4.em. Vincent        | 46 Double room ensuite                  | 2010-01-03  | 2010-01-03 |       2          |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   | 2010-01-01  | 2010-01-10 |       6          |

When I extract the availability into a file
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following bookings will be saved in the file
room name               | type | number of person | date       |
13. The Blue Brothers   | BED  |    4             | 2010-01-02 |
13. The Blue Brothers   | BED  |    4             | 2010-01-03 |
17. 4.em. Vincent       | ROOM |    2             | 2010-01-03 |
21. Nathan              | ROOM |    6             | 2010-01-02 |
21. Nathan              | ROOM |    6             | 2010-01-03 |
18. 4.em. Jules         | ROOM |    2             | 2010-01-02 |
18. 4.em. Jules         | ROOM |    2             | 2010-01-03 |









Scenario 3: Extract some multi-day bookings with room changes into file
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no prices set
Start date | end date
2010-01-01 | 2010-12-31

Given there is no availability extracted

Given the following multi day bookings exist
room name                | room type                               | first night | last night | number of person | room change date | new room name |
13. The Blue Brothers    | 35 6 bed Dorm                           | 2010-01-02  | 2010-01-05 |       4          |                  |               |
18. 4.em. Jules          | 46 Double room ensuite                  | 2010-01-02  | 2010-01-06 |       2          | 2010-01-02       | 16. 4.em. Mia |
18. 4.em. Jules          | 46 Double room ensuite                  | 2010-01-02  | 2010-01-02 |       2          |                  |               |
17. 4.em. Vincent        | 46 Double room ensuite                  | 2010-01-03  | 2010-01-03 |       2          |                  |               |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   | 2010-01-01  | 2010-01-10 |       6          |                  |               |

When I extract the availability into a file
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following bookings will be saved in the file
room name               | type | number of person | date       |
13. The Blue Brothers   | BED  |    4             | 2010-01-02 |
13. The Blue Brothers   | BED  |    4             | 2010-01-03 |
17. 4.em. Vincent       | ROOM |    2             | 2010-01-03 |
21. Nathan              | ROOM |    6             | 2010-01-02 |
21. Nathan              | ROOM |    6             | 2010-01-03 |
16. 4.em. Mia           | ROOM |    2             | 2010-01-02 |
18. 4.em. Jules         | ROOM |    2             | 2010-01-02 |
18. 4.em. Jules         | ROOM |    2             | 2010-01-03 |
