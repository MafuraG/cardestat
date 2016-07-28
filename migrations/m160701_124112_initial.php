<?php

use yii\db\Migration;
use yii\db\Schema;

class m160701_124112_initial extends Migration {
    public function safeUp() {
        $this->createTable('user', [
            'id' => Schema::TYPE_PK,
            'username' => 'varchar(32) not null unique',
            'authKey' => 'varchar(32)',
            'accessToken' => 'varchar(32)',
            'hash' => 'varchar(60) not null',
        ]);
        $this->insert('user', [
            'username' => 'admin',
            'hash' => '$2y$13$yQ0zD/9LKz76Jlifsus8jeMXLXWtQG5AuFyF.cwzjNGFv1Ajeewiq' // password 'secret'
        ]);
        $this->insert('user', [
            'username' => 'pepe',
            'hash' => '$2y$13$yQ0zD/9LKz76Jlifsus8jeMXLXWtQG5AuFyF.cwzjNGFv1Ajeewiq' // password 'secret'
        ]);
        $this->createTable('item', [
            'id' => Schema::TYPE_PK,
            'name' => 'varchar(48) not null',
            'parent_id' => 'integer references item (id) on delete cascade',
            'unique (name, parent_id)'
        ]);
        $this->createTable('item_reading_group', [
            'id' => Schema::TYPE_PK,
            'from' => 'date not null',
            'to' => 'date not null',
            'created_at' => 'timestamp not null',
            //'created_by' => 'integer not null references to usr (id)'
        ]);
        $this->createTable('item_reading', [
            'id' => Schema::TYPE_PK,
            'item_reading_group_id' => 'integer not null references item_reading_group (id) on delete cascade',
            'count' => 'integer check (count >= 0)',
            'item_id' => 'integer not null references item (id) on delete cascade',
        ]);
        $this->execute('
            create view item_extended as 
                select i.*, tree.root_id, tree.level, tree.path
                    from (
                        with recursive tree(root_id, id, level, path) as (
                            select i.id, i.id, 0, i.name::text
                            from item i
                            where parent_id is null
                            union all
                            select root_id, i.id, level + 1, path || \'\\\' || name
                            from tree, item i
                            where tree.id = i.parent_id)
                        select * from tree) as tree, item i
                    where tree.id = i.id
                    order by level asc
        ');
        $this->execute('
            create view item_reading_extended as 
                select ir.*, i.name, i.parent_id, i.root_id, i.level, i.path
                from item_reading ir 
                     inner join item_extended i on (i.id = ir.item_id)
        ');
        $this->execute('
            create view user_extended as 
            select u.*, array_agg(ai.name) as roles
            from "user" u 
                 left join auth_assignment aa on (u.id = aa.user_id::integer)
                 left join auth_item ai on (ai.name = aa.item_name and ai.type = 1)
            group by u.id, u.username, u."authKey", u."accessToken", u.hash;
        ');
        $auth = Yii::$app->authManager;
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->assign($admin, 1);
    }

    public function safeDown() {
        $this->execute('drop view item_reading_extended');
        $this->execute('drop view item_extended');
        $this->dropTable('item_reading');
        $this->dropTable('item_reading_group');
        $this->dropTable('item');
        $this->dropTable('user');
    }
}
