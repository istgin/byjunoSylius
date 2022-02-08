<h1>Installation</h1>

 - add repository to composer:<br>
<code>
{
    "type": "vcs",
    "url": "https://github.com/istgin/byjunoSylius"
}
add package "ij/byjuno-plugin": "*"
</code><br><br>
- add file packages/ij_sylius_byjuno.yaml<br>

imports: <br>
    - { resource: "@IjSyliusByjunoPlugin/Resources/config/config.yaml" }
    <br><br>
- add file  routes/ij_sylius_byjuno.yaml<br>

ij_sylius_byjuno:<br>
    resource: "@IjSyliusByjunoPlugin/Resources/config/routes.yaml"



