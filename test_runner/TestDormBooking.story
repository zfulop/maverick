
Scenario 1: book dorm beds into a dorm where all 3 can fit
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 | 2010-01-04 | 
11. Lemon                | 39 Double room with shared bathroom     |            |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |    4       |     4      |     4      |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |            |            |            |
19. 4.em. Butch          | 46 Double room ensuite                  |            |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |            |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When the following booking arrives from myallocator
room type    | start date   | end date    | currency | units | price  | comission |
             | 2010-01-02   | 2010-01-03  | EUR      |       |        |           |
6 bed Dorm   | 2010-01-02   | 2010-01-03  |          | 3     | 21     | 0         |

Then the following bookings will exist in the db
room type    | room          | first night | last night | booking type | room payment |
6 bed Dorm   | 14. Mss Peach | 2010-01-02  | 2010-01-03 | BED          | 7            |
6 bed Dorm   | 14. Mss Peach | 2010-01-02  | 2010-01-03 | BED          | 7            |
6 bed Dorm   | 14. Mss Peach | 2010-01-02  | 2010-01-03 | BED          | 7            |



Scenario 2: book dorm beds but use 2 rooms
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 | 2010-01-04 | 
11. Lemon                | 39 Double room with shared bathroom     |            |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |    4       |     4      |     4      |
14. Mss Peach            | 35 6 bed Dorm                           |    2       |     2      |     2      |
15. Mr Green             | 42 10 bed Dorm                          |            |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |            |            |            |
19. 4.em. Butch          | 46 Double room ensuite                  |            |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |            |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When the following booking arrives from myallocator
room type    | start date   | end date    | currency | units | price  | comission |
             | 2010-01-02   | 2010-01-03  | EUR      |       |        |           |
6 bed Dorm   | 2010-01-02   | 2010-01-03  |          | 6     | 42     | 0         |

Then the following bookings will exist in the db
room type    | room                  | first night | last night | booking type | room payment |
6 bed Dorm   | 13. The Blue Brothers | 2010-01-02  | 2010-01-03 | BED          | 7            |
6 bed Dorm   | 13. The Blue Brothers | 2010-01-02  | 2010-01-03 | BED          | 7            |
6 bed Dorm   | 13. The Blue Brothers | 2010-01-02  | 2010-01-03 | BED          | 7            |
6 bed Dorm   | 13. The Blue Brothers | 2010-01-02  | 2010-01-03 | BED          | 7            |
6 bed Dorm   | 13. The Blue Brothers | 2010-01-02  | 2010-01-03 | BED          | 7            |
6 bed Dorm   | 13. The Blue Brothers | 2010-01-02  | 2010-01-03 | BED          | 7            |

Then the following booking room changes will exist in the db
original room         | new room            | date of room change |
13. The Blue Brothers | 14. Mss Peach       | 2010-01-02          |
13. The Blue Brothers | 14. Mss Peach       | 2010-01-02          |
13. The Blue Brothers | 14. Mss Peach       | 2010-01-02          |
13. The Blue Brothers | 14. Mss Peach       | 2010-01-02          |
13. The Blue Brothers | 14. Mss Peach       | 2010-01-03          |
13. The Blue Brothers | 14. Mss Peach       | 2010-01-03          |
13. The Blue Brothers | 14. Mss Peach       | 2010-01-03          |
13. The Blue Brothers | 14. Mss Peach       | 2010-01-03          |
