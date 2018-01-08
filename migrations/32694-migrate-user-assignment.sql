INSERT INTO nutzerzuordnung (nutzerid, behoerdenid)
(SELECT n.NutzerID, n.BehoerdenID
FROM nutzer n
  LEFT JOIN nutzerzuordnung nz ON n.NutzerID = nz.nutzerid
    WHERE nz.nutzerid IS NULL);
