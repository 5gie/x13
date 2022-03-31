{extends file="page.tpl"}

{block name='content'}
  {if $hello}
    <h1>{$hello.title}</h1>
    <div class="text">
      {$hello.text nofilter}
    </div>
  {/if}
{/block}