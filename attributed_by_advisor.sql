select case when effective_attribution.advisor_id is null then archived.advisor_id else effective_attribution.advisor_id end as joined_advisor_id,
       round(sum((coalesce(effective_attribution.amount_euc, 0) + coalesce(archived.sum, 0))/ 100.), 2),
       round(sum((coalesce(effective_attribution.amount_euc, 0))/ 100.), 2),
       round(sum((coalesce(archived.sum, 0))/ 100.), 2)
FROM "advisor" 
     JOIN "effective_attribution" ON "advisor"."id" = "effective_attribution"."advisor_id"
     JOIN "transaction" ON "effective_attribution"."transaction_id" = "transaction"."id"
     JOIN (
         select min(issued_at) issued_at, transaction_id
         from "invoice" 
         where issued_at between '2016-01-01' and '2016-12-31'
         group by transaction_id
     ) min_invoice ON "transaction"."id" = "min_invoice"."transaction_id"
     full JOIN (
         select sum(attributed_euc), advisor_id
         from "archived_attribution"
              LEFT JOIN "archived_invoice" ON "archived_attribution"."archived_invoice_id" = "archived_invoice"."id" 
         where (month between '2012-01-01' and '2015-12-31')  
         group by advisor_id
     ) archived ON false 
WHERE (issued_at between '2016-01-01' and '2016-12-31' or issued_at is null)
group by joined_advisor_id
order by joined_advisor_id;
