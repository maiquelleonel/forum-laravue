<?php

use Artesaos\Defender\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            "admin:dashboard"           => "Ver Dashboard",

            "admin:users.index"         => "Ver usuários",
            "admin:deleted-users.index" => "Ver usuários excluídos",
            "admin:users.create"        => "Ver formulário de cadastro de usuário",
            "admin:users.edit"          => "Ver formulário de edição de usuário",
            "admin:users.store"         => "Cadastrar novos usuários",
            "admin:users.update"        => "Atualizar dados de usuários",

            "admin:customers.index"     => "Ver clientes",
            "admin:customers.create"    => "Ver formulário de cadastro de cliente",
            "admin:customers.canceled"  => "Ver clientes com pagamento não autorizado",
            "admin:customers.interested"=> "Ver clientes interessados",
            "admin:customers.show"      => "Ver tela de pedidos do cliente",
            "admin:customers.edit"      => "Ver formuário de edição de cliente",
            "admin:customers.update"    => "Atualizar dados de cliente",
            "admin:customers.store"     => "Cadastrar novos clientes",

            "admin:delayed-customers.index"     => "Ver clientes (Vendas)",
            "admin:delayed-customers.canceled"  => "Ver clientes com pagamento não autorizado (Vendas)",
            "admin:delayed-customers.interested"=> "Ver clientes interessados (Vendas)",

            "admin:orders.index"        => "Ver pedidos",
            "admin:orders.store"        => "Cadastrar novos pedidos",
            "admin:orders.update"       => "Atualizar dados de pedidos",
            "admin:orders.update-status"=> "Atualizar status de pedidos",
            "admin:orders.show"         => "Ver informações de pedidos",
            "admin:orders.integrate"    => "Integrar pedidos manualmente",
            "admin:orders.integrate-now" => "Integrar pedidos manualmente sem validar",
            "admin:orders.without-invoice"=> "Ver pedidos sem Nota Fiscal",
            "admin:orders.update-invoice-number"=> "Atualizar número de pedido e nota fiscal de pedidos",
            "admin:orders.store-upsell"=> "Criar Pedido Complementar",

            "admin:orderitembundle.store"       => "Incluir pacotes no pedido",
            "admin:orderitemproduct.store"      => "Incluir produtos no pedido",
            "admin:orderitembundle.destroy"     => "Excluir pacotes do pedido",
            "admin:orderitemproduct.destroy"    => "Excluir produtos no pedido",
            "admin:checkout.credit-card"        => "Processar pagamentos de Cartão de Crédito",
            "admin:checkout.boleto"             => "Processar pagamentos de Boleto",
            "admin:checkout.pagseguro"          => "Processar pagamentos de PagSeguro",


            "admin:report.charts"               => "Ver relatório gráficos",
            "admin:report.vendors"              => "Ver relatório de vendedores",
            "admin:report.bundles"              => "Ver relatório de pacotes",
            "admin:report.comparative-tables"   => "Ver relatório tabela diária de vendas",
            "admin:report.extract-seller"       => "Ver relatório de vendas (usuário autenticado)",
            "admin:report.campaigns-orders"     => "Ver relatório de Campanhas de Pedidos",
            "admin:report.campaigns-leads"      => "Ver relatório de Campanhas de Leads",
            "admin:report.campaigns-orders.details"=> "Ver relatório detalhado de Campanhas de Pedidos",
            "admin:report.campaigns-leads.details" => "Ver relatório detalhado de Campanhas de Leads",
            "admin:report.products-sold"        => "Ver relatório de produtos vendidos",
            "admin:report.bundles-sold"         => "Ver relatório de pacotes vendidos",

            "admin:billing.campaign-costs.index"=> "Ver custos de Campanhas",
            "admin:billing.campaign-costs.store"=> "Adicionar custos de Campanhas",

            "admin:transaction.refund"  => "Estornar transações",
            "admin:transaction.capture" => "Capturar transações",

            "admin:product.index"       => "Ver produtos",
            "admin:product.create"      => "Ver formulário de cadastro de produto",
            "admin:product.edit"        => "Ver formulário de edição de produto",
            "admin:product.update"      => "Atualizar dados de produtos",
            "admin:product.store"       => "Cadastrar novos produtos",

            "admin:bundle.index"        => "Ver pacotes",
            "admin:bundle.create"       => "Ver formulário de cadastro de pacote",
            "admin:bundle.edit"         => "Ver formulário de edição de pacote",
            "admin:bundle.update"       => "Atualizar dados de pacotes",
            "admin:bundle.store"        => "Cadastrar novos pacotes",

            "admin:site.index"          => "Ver sites",
            "admin:site.create"         => "Ver formulário de cadastro de site",
            "admin:site.edit"           => "Ver formulário de edição de site",
            "admin:site.store"          => "Cadastrar novos sites",
            "admin:site.update"         => "Atualizar dados de sites",
            "admin:site.destroy"        => "Excluir Sites",

            "admin:site.updateSession"  => "Escolher Filtro de Sites",

            "admin:company.index"       => "Ver empresas",
            "admin:company.create"      => "Ver formulário de cadastro de empresa",
            "admin:company.edit"        => "Ver formulário de edição de empresa",
            "admin:company.store"       => "Cadastrar novas empresas",
            "admin:company.update"      => "Atualizar dados de empresas",
            "admin:company.destroy"     => "Excluir empresas",

            "admin:pixel.index"         => "Ver pixels",
            "admin:pixel.create"        => "Ver formulário de cadastro de pixel",
            "admin:pixel.edit"          => "Ver formulário de ediçãod de pixels",
            "admin:pixel.store"         => "Cadastrar novos pixels",
            "admin:pixel.update"        => "Atualizar dados de pixels",
            "admin:pixel.destroy"       => "Excluir pixels",

            "admin:payment-setting.index"   => "Ver formas de pagamento",
            "admin:payment-setting.create"  => "Ver formulário de cadastro de formas de pagamento",
            "admin:payment-setting.edit"    => "Ver formulário de edição de formas de pagamento",
            "admin:payment-setting.store"   => "Cadastrar novas formas de pagemntos",
            "admin:payment-setting.update"  => "Atualizar dados de formas de pagamento",
            "admin:payment-setting.destroy" => "Excluir formas de pagamento",

            "admin:upsell.index"        => "Ver Upsells",
            "admin:upsell.create"       => "Ver formulário de cadastro de upsell",
            "admin:upsell.edit"         => "Ver formulário de edição de upsell",
            "admin:upsell.store"        => "Cadastrar novos upsells",
            "admin:upsell.update"       => "Atualizar dados de upsell",
            "admin:upsell.destroy"      => "Excluir upsell",

            "admin:role.index"          => "Ver atribuições",
            "admin:role.create"         => "Ver formulário de cadastro de atribuições",
            "admin:role.edit"           => "Ver formulário de edição de atribuições",
            "admin:role.store"          => "Cadastrar novas atribuições",
            "admin:role.update"         => "Atualizar dados de atribuições",
            "admin:role.destroy"        => "Excluir Atribuições",

            "admin:permission.index"    => "Ver permissões",
            "admin:permission.create"   => "Ver formulário de cadastro de permissões",
            "admin:permission.edit"     => "Ver formulário de edição de permissões",
            "admin:permission.store"    => "Cadastrar novas permissões",
            "admin:permission.update"   => "Atualizar dados de permissões",
            "admin:permission.destroy"  => "Excluir Permissões",

            "admin:erp-setting.index"   => "Ver Integrações ERP",
            "admin:erp-setting.edit"    => "Ver formulário de edição de ERP",
            "admin:erp-setting.update"  => "Atualizar configurações de ERP",
            "admin:erp-setting.create"  => "Ver formulário de cadastro de Configuração de ERP",
            "admin:erp-setting.store"   => "Cadastrar novas configurações de ERP",

            "admin:product-link.index"   => "Ver Links de Produtos",
            "admin:product-link.edit"    => "Ver formulário de edição de Link de Produto",
            "admin:product-link.update"  => "Atualizar Links de Produtos",
            "admin:product-link.create"  => "Ver formulário de cadastro de Link de Produto",
            "admin:product-link.store"   => "Cadastrar novos links de produtos",

            "admin:bundle-group.index"   => "Ver Grupos de Pacotes",
            "admin:bundle-group.edit"    => "Ver formulário de edição de Grupo de Pacote",
            "admin:bundle-group.update"  => "Atualizar Grupo de Pacotes",
            "admin:bundle-group.create"  => "Ver formulário de cadastro de Grupo de Pacote",
            "admin:bundle-group.store"   => "Cadastrar Grupo de Pacote",

            "admin:orders.update-vendor" => "Alterar vendedor de pedido",

            "admin:additional.index"     => "Ver Adicionais",
            "admin:additional.create"    => "Ver formulário de cadastro de adicional",
            "admin:additional.edit"      => "Ver formulário de edição de adicional",
            "admin:additional.store"     => "Cadastrar novos adicionais",
            "admin:additional.update"    => "Atualizar dados de adicionais",
            "admin:additional.destroy"   => "Excluir Adicional",

            "admin:config-commission-group.index"     => "Ver Grupos de Comissionamento",
            "admin:config-commission-group.create"    => "Ver formulário de cadastro de Grupo de Comissionamento",
            "admin:config-commission-group.edit"      => "Ver formulário de edição de Grupo de Comissionamento",
            "admin:config-commission-group.store"     => "Cadastrar novos Grupos de Comissionamento",
            "admin:config-commission-group.update"    => "Atualizar Grupo de Comissionamento",
            "admin:config-commission-group.destroy"   => "Excluir Grupo de Comissionamento",

            "admin:config-commission-rule.index"     => "Ver Regras de Comissionamento",
            "admin:config-commission-rule.create"    => "Ver formulário de cadastro de Regra de Comissionamento",
            "admin:config-commission-rule.edit"      => "Ver formulário de edição de Regra de Comissionamento",
            "admin:config-commission-rule.store"     => "Cadastrar novas Regras de Comissionamento",
            "admin:config-commission-rule.update"    => "Atualizar Regra de Comissionamento",
            "admin:config-commission-rule.destroy"   => "Excluir Regra de Comissionamento",

            "admin:sales-commission.index"           => "Ver Comissões",
            "admin:sales-commission.create"          => "Ver formulário de cadastro de Comissões",
            "admin:sales-commission.edit"            => "Ver formulário de edição de Comissões",
            "admin:sales-commission.store"           => "Cadastrar novas Comissões",
            "admin:sales-commission.update"          => "Atualizar Comissões",
            "admin:sales-commission.destroy"         => "Excluir Comissões",

            "admin:my-sales-commission.index"        => "Ver Minhas Comissões",
            "admin:my-sales-commission.create"       => "Ver formulário de cadastro de Minhas Comissões",
            "admin:my-sales-commission.edit"         => "Ver formulário de edição de Minhas Comissões",
            "admin:my-sales-commission.store"        => "Cadastrar Minhas Comissões",
            "admin:my-sales-commission.update"       => "Atualizar Minhas Comissões",
            "admin:my-sales-commission.destroy"      => "Excluir Minhas Comissões",

            "admin:post-back.index"                  => "Ver PostBacks",
            "admin:post-back.create"                 => "Ver formulário de cadastro de PostBack",
            "admin:post-back.edit"                   => "Ver formulário de edição de PostBack",
            "admin:post-back.store"                  => "Cadastrar PostBacks",
            "admin:post-back.update"                 => "Atualizar PostBacks",
            "admin:post-back.destroy"                => "Excluir PostBacks",

            "admin:paid-commission.index"                  => "Ver Comissões Pagas",
            "admin:paid-commission.create"                 => "Ver formulário para pagar Comissão",
            "admin:paid-commission.store"                  => "Pagar Comissões",
            "admin:paid-commission.update"                 => "Atualizar Comissão paga",
            "admin:paid-commission.destroy"                => "Excluir Comissão Paga",

            "admin:link-generator.create"            => "Ver Formulário de Cadastro de Links de Campanhas",
            "admin:link-generator.store"             => "Gerar Links de Campanhas",

            "admin:currency.index"                  => "Ver Moedas",
            "admin:currency.create"                 => "Ver formulário de cadastro de moeda",
            "admin:currency.edit"                   => "Ver formulário de edição de moeda",
            "admin:currency.store"                  => "Cadastrar Moeda",
            "admin:currency.update"                 => "Atualizar Moeda",
            "admin:currency.destroy"                => "Excluir Moeda",

            "admin:affiliate-pixel.index"                  => "Ver Pixel de Afiliado",
            "admin:affiliate-pixel.create"                 => "Ver formulário de cadastro de Pixel de Afiliado",
            "admin:affiliate-pixel.edit"                   => "Ver formulário de edição de Pixel de Afiliado",
            "admin:affiliate-pixel.store"                  => "Cadastrar Pixel de Afiliado",
            "admin:affiliate-pixel.update"                 => "Atualizar Pixel de Afiliado",
            "admin:affiliate-pixel.destroy"                => "Excluir Pixel de Afiliado",

            "admin:api-key.index"                  => "Ver Chaves API",
            "admin:api-key.create"                 => "Ver formulário de cadastro de Chave API",
            "admin:api-key.edit"                   => "Ver formulário de edição de Chave API",
            "admin:api-key.store"                  => "Cadastrar Chave API",
            "admin:api-key.update"                 => "Atualizar Chave API",
            "admin:api-key.destroy"                => "Excluir Chave API",
        ];

        foreach ($permissions as $name => $readableName) {
            try {
                Permission::create([
                    "name"          => $name,
                    "readable_name" => $readableName
                ]);
                echo "Permissão adicionada: $readableName ($name)" . PHP_EOL;
            } catch (\Exception $e) {
//
            }
        }
    }
}
