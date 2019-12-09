<%
use Cake\Utility\Inflector;

$associations += ['BelongsTo' => [], 'HasOne' => [], 'HasMany' => [], 'BelongsToMany' => []];
$immediateAssociations = $associations['BelongsTo'];
$associationFields = collection($fields)
    ->map(function($field) use ($immediateAssociations) {
        foreach ($immediateAssociations as $alias => $details) {
            if ($field === $details['foreignKey']) {
                return [$field => $details];
            }
        }
    })
    ->filter()
    ->reduce(function($fields, $value) {
        return $fields + $value;
    }, []);

$groupedFields = collection($fields)
    ->filter(function($field) use ($schema) {
        return $schema->columnType($field) !== 'binary';
    })
    ->groupBy(function($field) use ($schema, $associationFields) {
        $type = $schema->columnType($field);
        if (isset($associationFields[$field])) {
            return 'string';
        }
        if (in_array($type, ['integer', 'float', 'decimal', 'biginteger'])) {
            return 'number';
        }
        if (in_array($type, ['date', 'time', 'datetime', 'timestamp'])) {
            return 'date';
        }
        return in_array($type, ['text', 'boolean']) ? $type : 'string';
    })
    ->toArray();

$groupedFields += ['number' => [], 'string' => [], 'boolean' => [], 'date' => [], 'text' => []];
$pk = "\$$singularVar->{$primaryKey[0]}";
%>
<section class="content-header">
  <h1>
    <?php echo __d('Qobo/Adminlte', '<%= $singularHumanName %>'); ?>
  </h1>
  <ol class="breadcrumb">
    <li>
    <?= $this->Html->link('<i class="fa fa-dashboard"></i> ' . __d('Qobo/Adminlte', 'Back'), ['action' => 'index'], ['escape' => false])?>
    </li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
<div class="row">
    <div class="col-md-12">
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-info"></i>
                <h3 class="box-title"><?php echo __d('Qobo/Adminlte', 'Information'); ?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <dl class="dl-horizontal">
                    <% if ($groupedFields['string']) : %>
                        <% foreach ($groupedFields['string'] as $field) : %>
                            <% if (isset($associationFields[$field])) :
                                $details = $associationFields[$field];
                                %>
                                <dt><?= __d('Qobo/Adminlte', '<%= Inflector::humanize($details['property']) %>') ?></dt>
                                <dd>
                                    <?= $<%= $singularVar %>->has('<%= $details['property'] %>') ? $<%= $singularVar %>-><%= $details['property'] %>-><%= $details['displayField'] %> : '' ?>
                                </dd>
                            <% else :
                                    if ($field != 'password') :%>
                                        <dt><?= __d('Qobo/Adminlte', '<%= Inflector::humanize($field) %>') ?></dt>
                                        <dd>
                                            <?= h($<%= $singularVar %>-><%= $field %>) ?>
                                        </dd>
                                    <% endif; %>
                            <% endif; %>
                        <% endforeach; %>
                    <% endif; %>
                        
                    <% if ($associations['HasOne']) : %>
                        <% foreach ($associations['HasOne'] as $alias => $details) : %>
                            <dt><?= __d('Qobo/Adminlte', '<%= Inflector::humanize(Inflector::singularize(Inflector::underscore($alias))) %>') ?></dt>
                            <dd>
                                <?= $<%= $singularVar %>->has('<%= $details['property'] %>') ? $this->Html->link($<%= $singularVar %>-><%= $details['property'] %>-><%= $details['displayField'] %>, ['controller' => '<%= $details['controller'] %>', 'action' => 'view', $<%= $singularVar %>-><%= $details['property'] %>-><%= $details['primaryKey'][0] %>]) : '' ?>
                            </dd>
                        <% endforeach; %>
                    <% endif; %>
                        
                    <% if ($groupedFields['number']) : %>
                        <% foreach ($groupedFields['number'] as $field) : %>
                            <% if ($field != $primaryKey[0]) :%>
                                <dt><?= __d('Qobo/Adminlte', '<%= Inflector::humanize($field) %>') ?></dt>
                                <dd>
                                    <?= $this->Number->format($<%= $singularVar %>-><%= $field %>) ?>
                                </dd>
                            <% endif; %>
                        <% endforeach; %>
                    <% endif; %>
                        
                    <% if ($groupedFields['date']) : %>
                        <% foreach ($groupedFields['date'] as $field) : %>
                            <% if (!in_array($field, ['created', 'modified', 'updated'])) : %>
                                <dt><%= "<%= __d('Qobo/Adminlte', '" . Inflector::humanize($field) . "') %>" %></dt>
                                <dd>
                                    <?= h($<%= $singularVar %>-><%= $field %>) ?>
                                </dd>
                                <% endif; %>
                        <% endforeach; %>
                    <% endif; %>
                        
                    <% if ($groupedFields['boolean']) : %>
                        <% foreach ($groupedFields['boolean'] as $field) : %>
                            <dt><?= __d('Qobo/Adminlte', '<%= Inflector::humanize($field) %>') ?></dt>
                            <dd>
                            <?= $<%= $singularVar %>-><%= $field %> ? __d('Qobo/Adminlte', 'Yes') : __d('Qobo/Adminlte', 'No'); ?>
                            </dd>
                        <% endforeach; %>
                    <% endif; %>
                        
                    <% if ($groupedFields['text']) : %>
                        <% foreach ($groupedFields['text'] as $field) : %>
                            <dt><?= __d('Qobo/Adminlte', '<%= Inflector::humanize($field) %>') ?></dt>
                            <dd>
                            <?= $this->Text->autoParagraph(h($<%= $singularVar %>-><%= $field %>)); ?>
                            </dd>
                        <% endforeach; %>
                    <% endif; %>
                </dl>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- ./col -->
</div>
<!-- div -->

<%
$relations = $associations['HasMany'] + $associations['BelongsToMany'];
foreach ($relations as $alias => $details):
    $otherSingularVar = Inflector::variable($alias);
    $otherPluralHumanName = Inflector::humanize(Inflector::underscore($details['controller']));
    %>
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <i class="fa fa-share-alt"></i>
                    <h3 class="box-title"><?= __d('Qobo/Adminlte', 'Related {0}', ['<%= $otherPluralHumanName %>']) ?></h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive no-padding">

                <?php if (!empty($<%= $singularVar %>-><%= $details['property'] %>)): ?>

                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <% foreach ($details['fields'] as $field): %>
                                    <% if (in_array($field, ['created', 'modified'])) { continue; } %>

                                    <th>
                                    <%= Inflector::humanize($field) %>
                                    </th>
                                        
                                <% endforeach; %>
                                    
                                <th>
                                    <?php echo __d('Qobo/Adminlte', 'Actions'); ?>
                                </th>
                            </tr>

                            <?php foreach ($<%= $singularVar %>-><%= $details['property'] %> as $<%= $otherSingularVar %>): ?>
                                <tr>
                                    <% foreach ($details['fields'] as $field): %>
                                    <% if (in_array($field, ['created', 'modified'])) { continue; } %>

                                    <td>
                                    <?= h($<%= $otherSingularVar %>-><%= $field %>) ?>
                                    </td>
                                    <% endforeach; %>

                                    <% $otherPk = "\${$otherSingularVar}->{$details['primaryKey'][0]}"; %>
                                    <td class="actions">
                                    <?= $this->Html->link(__d('Qobo/Adminlte', 'View'), ['controller' => '<%= $details['controller'] %>', 'action' => 'view', <%= $otherPk %>], ['class'=>'btn btn-info btn-xs']) %>
                                    <?= $this->Html->link(__d('Qobo/Adminlte', 'Edit'), ['controller' => '<%= $details['controller'] %>', 'action' => 'edit', <%= $otherPk %>], ['class'=>'btn btn-warning btn-xs']) %>
                                    <?= $this->Form->postLink(__d('Qobo/Adminlte', 'Delete'), ['controller' => '<%= $details['controller'] %>', 'action' => 'delete', <%= $otherPk %>], ['confirm' => __d('Qobo/Adminlte', 'Are you sure you want to delete # {0}?', <%= $otherPk %>), 'class'=>'btn btn-danger btn-xs']) %>    
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                                    
                        </tbody>
                    </table>

                <?php endif; ?>

                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>
<% endforeach; %>
</section>
