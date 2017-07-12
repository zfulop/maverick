
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
name                     | room type id | room type name                         |
11. Lemon                | 39           | Double room with shared bathroom       |
12. Yellow               | 39           | Double room with shared bathroom       |
13. The Blue Brothers    | 35           | 6 bed Dorm                             |
14. Mss Peach            | 35           | 6 bed Dorm                             |
15. Mr Green             | 42           | 10 bed dorm                            |
16. 4.em. Mia            | 46           | Double room ensuite                    |
18. 4.em. Jules          | 46           | Double room ensuite                    |
17. 4.em. Vincent        | 46           | Double room ensuite                    |
19. 4.em. Butch          | 46           | Double room ensuite                    |
20. 4.em. Honey          | 46           | Double room ensuite                    |
21. Nathan               | 69           | Deluxe Studio Apartment, Ferenciek     |
22. Simon                | 70           | Studio Apartment, Ferenciek            |
23. Kelly                | 72           | One-Bedroom Apartment 2.0, Ferenciek   |
24. Curtis               | 69           | Deluxe Studio Apartment, Ferenciek     |
25. Alisha               | 69           | Deluxe Studio Apartment, Ferenciek     |




Scenario 1: Book one of the empty private room
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 | 2010-01-04 | 
11. Lemon                | 39 Double room with shared bathroom     |            |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |            |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |   X        |    X       |
18. 4.em. Jules          | 46 Double room ensuite                  |     X      |            |    X       |
17. 4.em. Vincent        | 46 Double room ensuite                  |     X      |   X        |            |
19. 4.em. Butch          | 46 Double room ensuite                  |            |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |            |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When the following booking arrives from myallocator
room type           | start date   | end date    | currency | units | price  | comission |
                    | 2010-01-02   | 2010-01-03  | EUR      |       |        |           |
Double room ensuite | 2010-01-02   | 2010-01-03  |          | 1     | 40     | 0         |

Then the following bookings will exist in the db
room type           | room                            | first night | last night | booking type | room payment |
Double room ensuite | 19. 4.em. Butch;20. 4.em. Honey | 2010-01-02  | 2010-01-03 | ROOM         | 40           |


Scenario 2: Do one bookings then change the price
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 | 2010-01-04 | 
11. Lemon                | 39 Double room with shared bathroom     |            |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |            |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |     X      |   X        |    X       |
18. 4.em. Jules          | 46 Double room ensuite                  |     X      |   X        |    X       |
17. 4.em. Vincent        | 46 Double room ensuite                  |     X      |   X        |    X       |
19. 4.em. Butch          | 46 Double room ensuite                  |     X      |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |            |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When the following booking arrives from myallocator
room type           | start date   | end date    | currency | units | price  | comission | myallocatorid |
                    | 2010-01-02   | 2010-01-03  | EUR      |       |        |           | myallocid1234 |
Double room ensuite | 2010-01-02   | 2010-01-03  |          | 1     | 40     | 0         |               |

Then the following bookings will exist in the db
room type           | room            | first night | last night | booking type | room payment |
Double room ensuite | 20. 4.em. Honey | 2010-01-02  | 2010-01-03 | ROOM         | 40           |

When the following booking arrives from myallocator
room type           | start date   | end date    | currency | units | price  | comission | myallocatorid |
                    | 2010-01-02   | 2010-01-03  | EUR      |       |        |           | myallocid1234 |
Double room ensuite | 2010-01-02   | 2010-01-03  |          | 1     | 50     | 0         |               |

Then the following bookings will exist in the db
room type           | room            | first night | last night | booking type | room payment |
Double room ensuite | 20. 4.em. Honey | 2010-01-02  | 2010-01-03 | ROOM         | 50           |

