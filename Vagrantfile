# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

provision = <<-EOT
    rpm -ivh http://ftp.riken.jp/Linux/fedora/epel/6/i386/epel-release-6-8.noarch.rpm
    yum -y install ansible libselinux-python
    ansible-playbook /vagrant/provision/vagrant.yml --connection=local -vvv
EOT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.provider :virtualbox do |provider, override|
    provider.name = "larave-book"
    provider.customize ["modifyvm", :id, "--memory", "1024"]
  end

  config.vm.box = "opscode-centos65"
  config.vm.box_url = "http://opscode-vm-bento.s3.amazonaws.com/vagrant/virtualbox/opscode_centos-6.5_chef-provisionerless.box"

  config.vm.hostname = "larave-book.dev"
  config.vm.network :private_network, ip: "192.168.33.111"

  config.vm.provision :shell, :inline => provision
  config.vm.synced_folder "src", "/share", :mount_options => ["dmode=777", "fmode=666"]
  config.vm.synced_folder ".", "/vagrant", :mount_options => ["dmode=777", "fmode=644"]
end
